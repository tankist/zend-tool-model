<?php
class Skaya_Tool_Project_Context_Zf_TablesDecorator_Mysql
	extends Skaya_Tool_Project_Context_Zf_TablesDecorator_Abstract {

	const FOREIGN_KEYS_REGEXP = '$FOREIGN KEY.*?\((.*?)\).*?REFERENCES(.*?)\((.*?)\)$im';

	public static function parseForeignKeys(Zend_Db_Adapter_Abstract $adapter) {
		if (empty(self::$_foreignKeys)) {
			foreach(self::getTables($adapter) as $tableName) {
				$dependentTables = $refTables = array();

				$data = $adapter->fetchRow('SHOW CREATE TABLE '.$adapter->quoteTableAs($tableName));
				$data = $data['Create Table'];
				$keysCount = preg_match_all(self::FOREIGN_KEYS_REGEXP, $data, $keys);
				if ($keysCount > 0) {
					$filter = new Zend_Filter_Word_UnderscoreToCamelCase();
					for($i = 0;
					    $i<$keysCount,
					        $tableFrom = $tableName,
					        $columnFrom = trim($keys[1][$i], ' `'),
					        $tableTo = trim($keys[2][$i], ' `'),
					        $columnTo = trim($keys[3][$i], ' `');
					    $i++){
						if (!array_key_exists($tableTo, $dependentTables)) {
							$dependentTables[$tableTo] = array();
						}
						$dependentTables[$tableTo][] = $tableFrom;

						if (!array_key_exists($tableFrom, $refTables)) {
							$refTables[$tableFrom] = array();
						}
						$referenceName = $filter->filter($tableTo);
						if (!array_key_exists($referenceName, $refTables[$tableFrom])) {
							$refTables[$tableFrom][$referenceName] = array(
								'columns' => array($columnFrom),
								'refTableClass' => $tableTo,
								'refColumns' => array($columnTo)
							);
						}
						else {
							$refTables[$tableFrom]['columns'][$referenceName] = $columnFrom;
							$refTables[$tableFrom]['refColumns'][$referenceName] = $columnTo;
						}

					}
				}

				self::$_foreignKeys['dependent'] = array_merge_recursive(
					(array)self::$_foreignKeys['dependent'],
					(array)$dependentTables
				);
				self::$_foreignKeys['references'] = array_merge(
					(array)self::$_foreignKeys['references'],
					(array)$refTables
				);
			}
		}

		return self::$_foreignKeys;
	}

	public function getPrimaryKey(Zend_Db_Adapter_Abstract $adapter, $tableName) {
		$primaryKeys = array();
		$keysInfo = $adapter->fetchAll('SHOW INDEX FROM ' . $adapter->quoteTableAs($tableName));
		$keysInfo = array_filter($keysInfo, create_function('$key', 'return ($key["Key_name"] == "PRIMARY");'));
		if (count($keysInfo) == 1) {
			$primaryKeys = array_shift($keysInfo);
			$primaryKeys = $primaryKeys['Column_name'];
		}
		else {
			foreach ($keysInfo as $_k) {
				$primaryKeys[] = $_k['Column_name'];
			}
		}
		return $primaryKeys;
	}

}
