<?php

namespace Applejack21\LaravelActions\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeReadFile
{
	public function execute(string $modelName, string $fileName)
	{
		$lowerModel = Str::lower($modelName);
		$directoryPath = app_path("Actions/{$modelName}");
		$filePath = app_path("Actions/{$modelName}/{$fileName}");

		if (!File::isDirectory($directoryPath)) {
			File::makeDirectory($directoryPath, 0755, true, true);
		}

		$fileContent = <<<EOT
		<?php

		namespace App\Actions\\$modelName;

		use App\\Models\\$modelName;
		use Illuminate\Database\Eloquent\Collection;

		class Get{$modelName}s
		{
			public function execute(): Collection
			{
				return $modelName::all();
			}
		}
		EOT;

		File::put($filePath, $fileContent);
	}
}
