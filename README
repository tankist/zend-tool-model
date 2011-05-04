Zend Model Generators and toolset
=================================

Generators
----------

### Table generation

	zf create x-tables [filter-table-prefix]

Generates table classes for every table in DB. Of course db params must be already installed with

	zf db configure

You can define common prefix you used in your tables to produce classes without this prefix. For example, if you have tbl_users table and call generator as

	zf create x-tables tbl_

Then Model_DbTable_Users will be created. If no prefix will be given then Model_DbTable_TblUsers class will be generated.
	
### Mapper generation

	zf create x-mapper name [type] [module]

Generates mapper class. If Db type were provided then mapper class will be filled with pre-defined functions:

* get<Item>ById
* get<Items>
* get<Items>Paginator

### Collection generation

	zf create x-collection name [item-type] [module]

Generates collection class for given item type. Item type must be given without Model_ prefix.

### Service generation

	zf create x-service name [module]

Generates service class.

### Model generation

	zf create x-model name [module]

Generates model infrastructure. 