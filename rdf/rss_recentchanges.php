<?php

	require(dirname(__FILE__)."/../../rdfphp-api/api/RdfPHP.php");

	if(!defined("RSS_FILE"))
		define("RSS_FILE", "recentchanges.rss");

	$parser = new RdfParser();
	$model = $parser->generateModel(RSS_FILE);

	$rdql_query = '
		SELECT ?givenName, ?age
		WHERE 
			(?x, <v:age>, ?age),
			(?x, <vcard:N>, ?blank),
			(?blank, <vcard:Given>, ?givenName)
		USING 
			vcard FOR <http://www.w3.org/2001/vcard-rdf/3.0#>
			rss FOR <http://purl.org/rss/1.0#>
			v FOR <http://sampleVocabulary.org/1.3/People#>';

	// query the model
	$res = $model->rdqlQuery($rdql_query);

	// show result
	RdqlEngine::writeQueryResultAsHtmlTable($res);

?>
