<?php

namespace Applejack21\LaravelActions\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeDeleteFile
{
	public function execute(string $modelName, string $fileName, bool $permaDelete = false)
	{
		$lowerModel = Str::lower($modelName);
		$directoryPath = app_path("Actions/{$modelName}");
		$filePath = app_path("Actions/{$modelName}/{$fileName}");
		$permaVariable = "$" . "perma";

		if (!File::isDirectory($directoryPath)) {
			File::makeDirectory($directoryPath, 0755, true, true);
		}

		if (!$permaDelete) {
			$fileContent = <<<EOT
		<?php

		namespace App\Actions\\$modelName;

		use App\\Models\\$modelName;

		class Delete$modelName
		{
			public function execute($modelName $$lowerModel): $modelName|bool
			{
				\$deleted = $$lowerModel ->delete();

				// return the model if is utilises soft deletes as the record still exists
				if(in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($$lowerModel)) && \$deleted === true) {
					return tap($$lowerModel)->refresh();
				}

				return \$deleted;
			}
		}
		EOT;
		} else {
			$fileContent = <<<EOT
		<?php

		namespace App\Actions\\$modelName;

		use App\\Models\\$modelName;

		class Delete$modelName
		{
			public function execute($modelName $$lowerModel, bool $permaVariable = false): $modelName|bool
			{
				$permaVariable ? $$lowerModel ->forceDelete() : $$lowerModel ->delete();

				return $permaVariable ? true : tap($$lowerModel)->refresh();
			}
		}
		EOT;
		}

		File::put($filePath, $fileContent);
	}
}
