<?php
/**
 * AJAX functions used by Comments extension.
 */

$wgAjaxExportList[] = 'wfCommentSubmit';
function wfCommentSubmit( $page_id, $parent_id, $comment_text, $token ) {
	global $wgUser;

	// Blocked users cannot submit new comments, and neither can those users
	// without the necessary privileges. Also prevent obvious cross-site request
	// forgeries (CSRF)
	if (
		$wgUser->isBlocked() ||
		!$wgUser->isAllowed( 'comment' ) ||
		wfReadOnly() ||
		!$wgUser->matchEditToken( $token )
	)
	{
		return '';
	}

	if( $comment_text != '' ) {
		// To protect against spam, it's necessary to check the supplied text
		// against spam filters (but comment admins are allowed to bypass the
		// spam filters)
		if ( !$wgUser->isAllowed( 'commentadmin' ) && Comment::isSpam( $comment_text ) ) {
			return wfMessage( 'comments-is-spam' )->plain();
		}

		// If the comment contains links but the user isn't allowed to post
		// links, reject the submission
		if ( !$wgUser->isAllowed( 'commentlinks' ) && Comment::haveLinks( $comment_text ) ) {
			return wfMessage( 'comments-links-are-forbidden' )->plain();
		}

		$comment = new Comment( $page_id );
		$comment->setCommentText( $comment_text );
		$comment->setCommentParentID( $parent_id );
		$comment->add();

		if( class_exists( 'UserStatsTrack' ) ) {
			$stats = new UserStatsTrack( $wgUser->getID(), $wgUser->getName() );
			$stats->incStatField( 'comment' );
		}
	}
	return 'ok';
}

$wgAjaxExportList[] = 'wfCommentVote';
function wfCommentVote( $comment_id, $vote_value, $vg, $page_id, $token ) {
	global $wgUser;

	// Blocked users cannot vote, obviously, and neither can those users
	// without the necessary privileges
	if (
		$wgUser->isBlocked() ||
		!$wgUser->isAllowed( 'comment' ) ||
		wfReadOnly() ||
		!$wgUser->matchEditToken( $token )
	)
	{
		return '';
	}

	if( is_numeric( $comment_id ) && is_numeric( $vote_value ) ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'Comments',
			array( 'comment_page_id', 'comment_user_id', 'comment_username' ),
			array( 'CommentID' => $comment_id ),
			__METHOD__
		);
		$row = $dbr->fetchObject( $res );
		if( $row ) {
			$PageID = $row->comment_page_id;
			$comment = new Comment( $PageID );
			$comment->CommentID = $comment_id;
			$comment->setCommentVote( $vote_value );
			$comment->setVoting( $vg );
			$comment->addVote();
			$out = $comment->getCommentScore();

			if( class_exists( 'UserStatsTrack' ) ) {
				$stats = new UserStatsTrack( $wgUser->getID(), $wgUser->getName() );

				// Must update stats for user doing the voting
				if( $vote_value == 1 ) {
					$stats->incStatField( 'comment_give_plus' );
				}
				if( $vote_value == -1 ) {
					$stats->incStatField( 'comment_give_neg' );
				}

				// Also must update the stats for user receiving the vote
				$stats_comment_owner = new UserStatsTrack( $row->comment_user_id, $row->comment_username );
				$stats_comment_owner->updateCommentScoreRec( $vote_value );

				$stats_comment_owner->updateTotalPoints();
				if( $vote_value === 1 ) {
					$stats_comment_owner->updateWeeklyPoints( $stats_comment_owner->point_values['comment_plus'] );
					$stats_comment_owner->updateMonthlyPoints( $stats_comment_owner->point_values['comment_plus'] );
				}
			}

			return $out;
		}
	}
}

$wgAjaxExportList[] = 'wfCommentList';
/**
 * This should roughly do the same as Special:CommentAction...except that
 * it fucks up royally - it causes a *fatal* in Parser.php. Don't ask me why.
 * @param $page_id Integer: article ID number
 * @param $order Integer: ???
 * @param $pagerPage Integer: the page we are currently paged to
 * @return HTML output
 */
function wfCommentList( $page_id, $order, $pagerPage ) {
	$comment = new Comment( $page_id );
	$comment->setOrderBy( $order );
	$comment->setCurrentPagerPage( $pagerPage );
	if( isset( $_POST['shwform'] ) && $_POST['shwform'] == 1 ) {
		$output .= $comment->displayOrderForm();
	}
	$output .= $comment->display();
	if( isset( $_POST['shwform'] ) && $_POST['shwform'] == 1 ) {
		$output .= $comment->displayForm();
	}
	return $output;
}

$wgAjaxExportList[] = 'wfCommentLatestID';
function wfCommentLatestID( $page_id ) {
	$comment = new Comment( $page_id );
	return $comment->getLatestCommentID();
}

$wgAjaxExportList[] = 'wfCommentBlock';
function wfCommentBlock( $comment_id, $user_id, $token ) {
	global $wgUser;

	// Do nothing when the database is in read-only mode or if the request was
	// forged
	if ( wfReadOnly() || !$wgUser->matchEditToken( $token ) ) {
		return '';
	}

	// Load user_name and user_id for person we want to block from the comment it originated from
	$dbr = wfGetDB( DB_SLAVE );
	$s = $dbr->selectRow(
		'Comments',
		array( 'comment_username', 'comment_user_id' ),
		array( 'CommentID' => $comment_id ),
		__METHOD__
	);
	if ( $s !== false ) {
		$user_id = $s->comment_user_id;
		$user_name = $s->comment_username;
	}

	$comment = new Comment( 0 );
	$comment->blockUser( $user_id, $user_name );

	if( class_exists( 'UserStatsTrack' ) ) {
		$stats = new UserStatsTrack( $user_id, $user_name );
		$stats->incStatField( 'comment_ignored' );
	}

	return 'ok';
}

$wgAjaxExportList[] = 'wfDeleteComment';
function wfDeleteComment( $pageId, $commentId, $token ) {
	global $wgUser;

	// Blocked users cannot delete comments, and neither can unprivileged ones.
	// Also check for database read-only status and ensure that this is _not_
	// a cross-site request forgery (CSRF) before proceeding further.
	if (
		$wgUser->isBlocked() ||
		!$wgUser->isAllowed( 'commentadmin' ) ||
		wfReadOnly() ||
		!$wgUser->matchEditToken( $token )
	)
	{
		return '';
	}

	$comment = new Comment( $pageId );
	$comment->setCommentID( $commentId );
	$comment->delete();

	return 'ok';
}