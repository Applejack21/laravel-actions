<?php

namespace Applejack21\LaravelActions\Actions;

use Illuminate\Support\Facades\DB;

class GetSchema
{
	public function execute(string $tableName)
	{
		// get the columns
		$schema = DB::getDoctrineSchemaManager();
		$columns = collect($schema->listTableColumns($tableName));

		// does the table exist?
		if (!$schema->tablesExist($tableName)) {
			return 'The table "' . $tableName . '" doesn\'t exist in the database. Please make sure you\'ve entered the name correctly or ran your migrations.';
		}

		// map the columns, getting info like if its a unique field or references to another table
		$columns = $columns->map(function ($col) use ($schema, $tableName) {
			$colInfo = $col->toArray();
			$typeName = $colInfo['type']->getName();

			// is the column a foreign key?
			$foreignKeys = $this->getForeignKeys($schema, $tableName);
			$isForeignKey = false;
			$referencedTable = null;

			foreach ($foreignKeys as $foreignKey) {
				if ($foreignKey->getLocalColumns() === [$colInfo['name']]) {
					$isForeignKey = true;
					$referencedTable = $foreignKey->getForeignTableName();
					break;
				}
			}

			// is the column unique constrained?
			$uniqueConstraints = $this->getTableIndexes($schema, $tableName);
			$isUnique = false;

			foreach ($uniqueConstraints as $constraint) {
				if ($constraint->isUnique() && in_array($colInfo['name'], $constraint->getColumns())) {
					$isUnique = true;
					break;
				}
			}

			return [
				...$colInfo,
				'type' => $typeName,
				'is_foreign_key' => $isForeignKey,
				'referenced_table' => $referencedTable,
				'is_unique' => $isUnique,
			];
		});

		return $columns;
	}

	private function getForeignKeys($schema, string $tableName)
	{
		return $schema->listTableForeignKeys($tableName);
	}

	private function getTableIndexes($schema, string $tableName)
	{
		return $schema->listTableIndexes($tableName);
	}
}
