<?php

namespace SMW\Tests\MediaWiki;

use SMW\MediaWiki\PageCreator;
use SMW\Tests\Util\Mock\MockTitle;

/**
 * @covers \SMW\MediaWiki\PageCreator
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 *
 * @license GNU GPL v2+
 * @since   2.0
 *
 * @author mwjames
 */
class PageCreatorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SMW\MediaWiki\PageCreator',
			 new PageCreator()
		);
	}

	public function testCreatePage() {

		$instance = new PageCreator();

		$this->assertInstanceOf(
			'\WikiPage',
			 $instance->createPage( MockTitle::buildMock( __METHOD__ ) )
		);
	}

	public function testCreateFilePage() {

		$instance = new PageCreator();

		$this->assertInstanceOf(
			'\WikiFilePage',
			 $instance->createFilePage( MockTitle::buildMock( __METHOD__ ) )
		);
	}

}
