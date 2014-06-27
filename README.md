#Cypher
This is a PHP adapter for the Neo4j Cypher end point. Currently the code is usable but it is a quick hack. Depends on the feedback, I will put more work on this(Unit test, Comment, etc...).

By the way, I'm a newbie in the open source space, if you have any suggestion, do open a new issues. You dont have to tag, I will read them all.

##Installation
You can install this package through composer. Either search through packagist or use the snippet below as a bare `composer.json`:
```
{
	"require": {
		"endyjasmi/cypher": "0.*"
	}
}
```

##Basic Usage
```
$cypher = new Cypher('http://localhost:7474');

$result = $cypher->statement('CREATE (person {endy}) RETURN person', array(
		'endy' => array(
			'name' => 'Endy Jasmi',
			'born' => 1990
		)
	))
	->execute();

echo $result[0]['person']['name']; // Endy Jasmi
```
`0` Index in the result represent first row.

`person` represent the identifier used in the query

`name` represent the property

##Bulk Statement in a single request
Notice how I set host to include username and password. I also skip setting the parameter in the statement method.
```
$cypher = new Cypher('https://user:pass@localhost:7473');

$results = $cypher->statement('MATCH (person {name: "Endy Jasmi"}) return person')
	->statement('CREATE (n) RETURN id(n) AS id')
	->execute();

echo $results[0][0]['person']['name']; // Result from the first statement
echo $result[1][0]['id'] // Result from the second statement
```

##Transaction
Notice how I skipped setting the host to use the default setting which is `http://localhost:7474`.
```
$cypher = new Cypher;

$cypher->beginTransaction();

$cypher->statement('CREATE (n) RETURN n')
	->execute();

$cypher->statement('CREATE (m) RETURN m')
	->execute();

$cypher->commit();
// or
$cypher->rollback();
```

##Errors
When there is errors in the transaction, `execute` method will return `false`. You can get the array of errors returned by the neo4j server throught cypher `errors` method.
```
$cypher = new Cypher;

$result = $cypher->statement('invalid query')
	->execute();

if (!$result) {
	$errors = $cypher->errors();
}

```

That is all there is to this library. It is meant to be built on top of.