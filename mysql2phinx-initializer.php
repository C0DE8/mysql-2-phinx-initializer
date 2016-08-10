<?php
/**
 * Easy command line tool for creating phinx initial migration code from an existing MySQL database.
 *
 * Commandline usage:
 * ```
 * $ mysql2phinx-initializer [database] [user] [password] > InitialMigration.php
 * ```
 */


if ($argc < 4) {
    echo 'Phinx MySQL initial migration generator (mysql2phinx-initializer) V1.0' . PHP_EOL;
    echo 'Usage: ' . $argv[0] . ' <database> <user> <password> [initial migration name|default: "InitialMigration"] > InitialMigration.php';
    echo PHP_EOL;
    exit;
}

$config = array(
    'name' => $argv[1], // assume database name
    'user' => $argv[2], // assume user name
    'pass' => $argv[3], // assume password
    'host' => $argc === 5 ? $argv[6] : 'localhost',
    'port' => $argc === 6 ? $argv[5] : '3306'
);

$initialMigrationName = (isset($argv[4])) ? $argv[4] : 'InitialMigration';

// #####################################################################################################################
// Functions
/**
 * @param $config
 * @return mysqli
 */
function getMysqliConnection($config)
{
    return new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
}

/**
 * @param $mysqli
 * @return array
 */
function getTables($mysqli)
{
    $res = $mysqli->query('SHOW TABLES');
    return array_map(function($a) { return $a[0]; }, $res->fetch_all());
}

/**
 * @param $mysqli
 * @param $table
 * @return mixed
 */
function getCreateTable($mysqli, $table)
{
    $res    = $mysqli->query('SHOW CREATE TABLE `' . $table . '`');
    $resArr = $res->fetch_assoc();
    return $resArr['Create Table'];
}

// #####################################################################################################################


$mysqli                  = getMysqliConnection($config);
$initialMigrationPHPCode = '';

foreach(getTables($mysqli) as $tableName) {
    if ('phinxlog' == $tableName) {
        continue;
    }
    $createTableString        = preg_replace("%\n%", "\n            ", getCreateTable($mysqli, $tableName));

    $initialMigrationPHPCode .= <<<CODE

        // Drop table if it already exists
         \$this->execute("DROP TABLE IF EXISTS `$tableName`;");
         
        // CREATE table string for table: "$tableName"
        \$this->execute("
            $createTableString
        ");

CODE;
}

$template = <<<TMPL
<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class $initialMigrationName extends AbstractMigration
{
    public function up()
    {
        // disable foreign key checks to ensure all tables can be initially created
        \$this->execute('SET FOREIGN_KEY_CHECKS=0;');
$initialMigrationPHPCode
        // re-arm foreign key checks
        \$this->execute('SET FOREIGN_KEY_CHECKS=1;');
    }
}

TMPL;

// final output to file of generated PHP code for initial phinx migration
file_put_contents('./' . date('YmdHis') . '_initial_migration.php', $template);
