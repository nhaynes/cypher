#Cypher [![Build Status](https://travis-ci.org/endyjasmi/cypher.svg?branch=master)](https://travis-ci.org/endyjasmi/cypher) [![Latest Stable Version](https://poser.pugx.org/endyjasmi/cypher/v/stable.svg)](https://packagist.org/packages/endyjasmi/cypher) [![License](https://poser.pugx.org/endyjasmi/cypher/license.svg)](https://packagist.org/packages/endyjasmi/cypher) [![Total Downloads](https://poser.pugx.org/endyjasmi/cypher/downloads.svg)](https://packagist.org/packages/endyjasmi/cypher)
PHP Library to help with using Neo4j Cypher Query Language. More information about Neo4j can be found [here](http://neo4j.com/) and information for cypher can be found [here](http://neo4j.com/docs/2.1.1/cypher-query-lang/). It also serve as a [laravel 4](http://laravel.com/) package and it is licensed under MIT so you can do whatever you want with it.

This library uses transaction rest api in Neo4j server hence only Neo4j 2.0 and above are supported. This library also uses guzzle 4 which requires PHP 5.4 and above.

##Installation
This library is available through [composer](https://packagist.org/packages/endyjasmi/cypher). If you dont know how to use composer, a tutorial can be found [here](http://code.tutsplus.com/tutorials/easy-package-management-with-composer--net-25530).

##Features
1. Sending cypher
2. Sending multiple cypher in a single request
3. Support transaction
4. Ported transaction status code to exception

##Basic use case
This use case and those following this assumes that Neo4j database is empty.

First, let's create a node;
```
$cypher = new Cypher;

$result = $cypher->statement(
	'CREATE (jeffrey:Person {information}) RETURN jeffrey.name AS name',
	array('information' => array(
		'name' => 'Jeffrey Jasmi',
		'born' => 1987
	))
)
->execute();

echo $result[0][0]['name']; // Jeffrey Jasmi
```
**Note on the result index:**

1. The first index `0` represent the first statement result. You will see more use of this in the next section.
2. The second index `0` represent the first row of the result which obviously in this case there's only a single row in the result.
3. The third index `name` represent the identifier specified in the return clause of the cypher.

###Returning a node
Rather then returning property in the node one by one, you can also return the node itself. By doing this, the result of the node will be an associative array;
```
$cypher = Cypher;

$result = $cypher->statement(
	'MATCH (jeffrey:Person {name: {name}) RETURN jeffrey',
	array(
		'name' => 'jeffrey'
	)
)
->execute();

echo $result[0][0]['jeffrey']['name']; // Jeffrey Jasmi
```
**Note on the result index (This will be the last time):**

1. The first index `0` represent the first statement result.
2. The second index `0` represent the first row of the result.
3. The third index `jeffrey` represent the identifier specified in the return clause. Because we are returning a node, this index will contain associative array of all the property of the node.
4. The fourth index `name` is the property of the node `jeffrey`.

###Query information
More information about certain query can be gathered; 
```
$cypher = Cypher;

$result = $cypher->statement('CREATE (donney:Person {information}) RETURN donney')
	->execute();

if ($result[0]->containsUpdates()) {
	$info = $result[0]->info();

	var_dump($info);

    echo $info['nodes_created']; // 1
}
```
Info dump from above result in:
```
array(
	'constraints_added' => 0,
	'constraints_removed' => 0,
	'contains_updates' => true,
	'indexes_added' => 0,
	'indexes_removed' => 0,
	'labels_added' => 0,
	'labels_removed' => 0,
	'nodes_created' => 1,
	'nodes_deleted' => 0,
	'properties_set' => 2,
	'relationship_created' => 0,
	'relationship_deleted' => 0
)
```

###Custom configuration
So far all our connection have been to connected with the default configuration options which is `http://localhost:7474`. In the event where you have custom configuration, you can easily provide a url to this library.
```
// Different scheme
$cypher = new Cypher('https://localhost:7474');

// Different host
$cypher = new Cypher('https://other.host:7474');

// Different port
$cypher = new Cypher('http://localhost:7473');

// Or you have setup basic authentication for neo4j server
$cypher = new Cypher('http://username:password@localhost:7474');
```

##Batch use case
By default, each time you execute a statement, a request is sent to the server and the server return result. For some who concern about overhead of establishing multiple http request, they might want to send multiple query in a single request. This can be done;
```
$cypher = Cypher;

$result = $cypher->statement(
	'MATCH (jeffrey:Person {name: {name}) RETURN jeffrey',
	array(
		'name' => 'jeffrey'
	)
)
->statement(
	'CREATE (endy:Person {information}) RETURN endy',
	array(
		'information' => array(
			'name' => 'Endy Jasmi',
			'born' => 1990
		)
	)
)
->execute();

echo $result[0][0]['jeffrey']['name']; // Jeffrey Jasmi
echo $result[1][0]['endy']['name']; // Endy Jasmi
```
Notice how the first index changes.

##Transaction use case
This library also support transaction. If you dont know what's transaction is, more information can be found [here](http://en.wikipedia.org/wiki/Database_transaction).
```
$cypher = new Cypher;

$cypher->beginTransaction();

$table_result = $cypher->statement('CREATE (table:Furniture) RETURN table')
	->execute();

$chair_result = $cypher->statement('CREATE (chair:Furniture) RETURN chair')
	->execute();

$cypher->commit();
// or
$cypher->rollback();
```

##Handling errors
This library port the status code returned by neo4j server as an php exception. For a list of valid status code which will be returned by neo4j server, you can refer [here](http://neo4j.com/docs/2.1.1/status-codes/).
```
$cypher = new Cypher;

try {
	$result = $cypher->statement('Invalid cypher statement')
		->execute();
} catch(InvalidSyntax $error) {
	echo $error->getMessage();
}
```

The exception class are implemented in a inheritance structure. Means that, rather then catching individual error, you can catch it's parent too. This design provides great control in handling errors.
```
$cypher = new Cypher;

try {
	$result = $cypher->statement('Invalid cypher statement')
		->execute();
} catch(InvalidSyntax $error) { // Catches specific error
	echo $error->getMessage();
} catch(Statement $error) { // Catches error which categorized under statement error
	echo $error->getMessage();
} catch(ClientError $error) { // Catches error which categorized under client error
	echo $error->getMessage();
} catch(Neo $error) { // Catches all neo4j returned error
	echo $error->getMessage();
}
```
The error exception class name are based on status codes which is returned by neo4j server. The list of status can be found [here](http://neo4j.com/docs/2.1.1/status-codes/).

##Using as laravel package
To use this as a laravel package, you would first need to `require` it in your application. Then register the service provider into the laravel framework. You can do so by adding `EndyJasmi\Cypher\ServiceProvider` into providers array in `app\config\app.php` file.

Once the provider has been registered, you are ready to use the library like following;
```
Route::get('/', function () {
	$result = Cypher::statement('MATCH (jeffrey:Person {name: {name}} RETURN jeffrey')
		->execute();

	return $result[0][0]->toJson();
});
```
All the method is the same as above with the difference only in how we call the cypher instance.

To customize the url, you would first need to publish the settings by typing in command `php artisan config:publish --path="vendor/endyjasmi/cypher/config" endyjasmi/cypher` in the framework root.

Then customize the `host` key array in `app/config/packages/endyjasmi/cypher/config.php`. Then you are good to go.

##Feedback
If you have any feature request, bug report, proposal, comment, or anything related to this library. Do not hesitate to [open a new issues](https://github.com/endyjasmi/cypher/issues/new).