<?php

namespace SMW\Tests\Util;

use SMW\Store;
use SMW\DIWikiPage;
use SMW\SemanticData;

use SMWRequestOptions as RequestOptions;

/**
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class InSemanticDataFetcher {

	/**
	 * @var Store
	 */
	private $store = null;

	/**
	 * @since 2.0
	 *
	 * @param Store $store
	 */
	public function __construct( Store $store ) {
		$this->store = $store;
	}

	/**
	 * @since 2.0
	 *
	 * @return SemanticData
	 */
	public function getSemanticData( DIWikiPage $subject ) {

		$requestOptions = new RequestOptions();
		$requestOptions->sort = true;

		$semanticData = new SemanticData( $subject );

		$incomingProperties = $this->store->getInProperties( $subject, $requestOptions );

		foreach ( $incomingProperties as $property ) {
			$values = $this->store->getPropertySubjects( $property, null );

			foreach ( $values as $value ) {
				$semanticData->addPropertyObjectValue( $property, $value );
			}
		}

		return $semanticData;
	}

}
