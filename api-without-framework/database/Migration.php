<?php


use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class Migration
{
    /**
     *
     */
    public function migrate(): void
    {
        $this->makeMigrationsTable();
        foreach ($this->readMigrationClasses() as $migrationClass) {
            list($fileName, $className, $pathName) = $migrationClass;
            $exits = Manager::table('migrations')->where('name', $fileName)->exists();
            if (!$exits) {
                $this->migrateClass($fileName, $className, $pathName);
            }
        }
    }

    /**
     * @param string $fileName
     * @param string $className
     * @param string $pathName
     */
    protected function migrateClass(string $fileName, string $className, string $pathName): void
    {
        require_once $pathName;
        $class = new $className();

        $class->up();
        Manager::table('migrations')->insert([[
            'name' => $fileName,
            'created_at' => Carbon::now()
        ]]);
    }

    /**
     * @return \Generator&string[]
     */
    protected function readMigrationClasses(): \Generator
    {
        $finder = new Finder();
        foreach ($finder->in(__DIR__ . DIRECTORY_SEPARATOR . 'migrations')->files() as $fileInfo) {
            $fileName = $fileInfo->getFilenameWithoutExtension();
            $className = Str::studly(preg_replace('/^\d+_/i', '', $fileName));
            yield [$fileName, $className, $fileInfo->getPathname()];
        }
    }

    /**
     *
     */
    protected function makeMigrationsTable(): void
    {
        try {
            Manager::table('migrations')->count();
        } catch (QueryException $exception) {
            if (Str::contains($exception->getMessage(), 'Undefined table')) {
                Manager::schema()->create('migrations', function (Blueprint $table) {
                    $table->string('name');
                    $table->primary('name');

                    $table->timestamp('created_at');
                });
            } else {
                throw $exception;
            }
        }
    }
}