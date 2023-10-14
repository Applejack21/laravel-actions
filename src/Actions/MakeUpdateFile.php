<?php

namespace Applejack21\LaravelActions\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeUpdateFile
{
	public function execute(string $modelName, string $fileName, array $validationRules)
	{
		$lowerModel = Str::lower($modelName);
		$directoryPath = app_path("Actions/{$modelName}");
		$filePath = app_path("Actions/{$modelName}/{$fileName}");

		if (!File::isDirectory($directoryPath)) {
			File::makeDirectory($directoryPath, 0755, true, true);
		}

		$validationRulesString = '';
		foreach ($validationRules as $field => $rules) {
			$validationRulesString .= "            '$field' => '$rules',\n";
		}

		// fix formatting to put a new rule on each line
		$formattedValidationRulesString = rtrim($validationRulesString, ",\n");

		$fileContent = <<<EOT
		<?php

		namespace App\Actions\\$modelName;

		use App\\Models\\$modelName;
		use Illuminate\\Support\\Facades\\Validator;

		class Update$modelName
		{
			public function execute($modelName $$lowerModel, array \$request): $modelName
			{
				\$this->validate(\$request, $$lowerModel);

				$$lowerModel ->update([
					...\$request,
				]);

				return tap($$lowerModel)->refresh();
			}

			private function validate(array \$request, $modelName $$lowerModel): array
			{
				return Validator::validate(\$request, [
		$formattedValidationRulesString
				]);
			}
		}
		EOT;

		File::put($filePath, $fileContent);
	}
}
