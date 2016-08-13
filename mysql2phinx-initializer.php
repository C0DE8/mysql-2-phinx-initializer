<?php
/**
 * MySQL 2 Phinx Initializer
 *
 * Easy command line tool for creating phinx initial migration code
 * from an existing MySQL database
 *
 * (The MIT license)
 * Copyright (c) 2016 Bjoern Ellebrecht (C0DE8)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated * documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package    C0DE8
 * @subpackage C0DE8\MySQL2Phinx
 */
namespace C0DE8\MySQLPhinx;

// setting error reporting
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

// version and release date
define('VERSION', '0.1.2');
define('STATE', 'beta');
define('DATE', '2016-08-13');


// #############################################################################
// Classes

/**
 * Class Color
 *
 * @package C0DE8\MySQLPhinx
 * @author  Bjoern Ellebrecht (C0DE8)
 */
class Color
{

    /**
     * @var string
     */
    const OPEN_COLOR    = "\033[";

    /**
     * @var string
     */
    const CLOSE_COLOR   =  "m";

    /**
     * @var string
     */
    const DEFAULT_COLOR = "\033[0m";

    /**
     * @var string
     */
    const DEFAULT_BACKGROUND_COLOR = '40';


    /**
     * @var array
     */
    protected static $_foregroundColor = array(
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    );

    /**
     * @var array
     */
    protected static $_backgroundColor = array(
        'black'        => self::DEFAULT_BACKGROUND_COLOR,
        'red'          => '41',
        'green'        => '42',
        'yellow'       => '43',
        'blue'         => '44',
        'magenta'      => '45',
        'cyan'         => '46',
        'light_gray'   => '47',
    );


    /**
     * @param  string $message
     * @param  null   $foregroundColor
     * @param  string $backgroundColor
     * @return string $output
     */
    public static function make(
        $message,
        $foregroundColor = null,
        $backgroundColor = 'black'
    ) {
        $output = '';

        if (isset(self::$_foregroundColor[$foregroundColor])) {
            $output .= self::enclose(self::$_foregroundColor[$foregroundColor]);
        }

        if (isset(self::$_backgroundColor[$backgroundColor])) {
            $output .= self::enclose(self::$_backgroundColor[$backgroundColor]);
        }

        return $output . $message . self::DEFAULT_COLOR;
    }

    /**
     * @param  $color
     * @return string
     */
    protected static function enclose($color)
    {
        return  self::OPEN_COLOR . $color . self::CLOSE_COLOR;
    }

}

/**
 * Class Console
 *
 * @package C0DE8\MySQLPhinx
 * @author  Bjoern Ellebrechtr (C0DE8)
 */
class Console
{

    /**
     * @var int
     */
    const MIN_ARG_COUNT = 4;


    /**
     * raw argv data
     *
     * @var array
     */
    protected $_argv = array();

    /**
     * short options
     *
     * "d:";  // Required value | database
     * "u:";  // Required value | user
     * "p:";  // Required value | password
     *
     * "h::"; // Optional value | host     | ip or histname of database server (default: localhost)
     * "t::"; // Optional value | port     | mysql server port (default: 3306)
     * "n::"; // Optional value | name     | class name of initial migration (default: InitialMigration)
     * "s::"; // Optional value | skip     | skip table(s) (default: "phinxlog")
     * "f::"; // Optional value | format   | format of filename prefix (default: 'YmdHis'; date format; result in YYYYMMDDHHMMSS)
     * "o::"; // Optional value | output   | filename / output (default: "{format_prefix}_initial_migration.php")
     *
     * @var string
     */
    protected $_shortOpts = "d:u:p:h::t::n::s::f::o::";

    /**
     * long options
     *
     * @var array
     */
    protected $_longOpts = array(
        "database:", // Required value | database
        "user:",     // Required value | user
        "pass:",     // Required value | password
        "host::",    // Optional value | host          (default: localhost)
        "port::",    // Optional value | port          (default: 3306)
        "name::",    // Optional value | name          (default: InitialMigration)
        "skip::",    // Optional value | skip table(s) (default: "phinxlog")
        "format::",  // Optional value | prefixformat  (default: 'YmdHis'; date format; result in YYYYMMDDHHMMSS)
        "output::",  // Optional value | output        (default: "{format_prefix}_initial_migration.php")
    );


    /**
     * Constructor
     *
     * @param array $argv
     */
    public function __construct(array $argv)
    {
        $this->_argv = $argv;
    }

    /**
     * @return bool
     */
    public function hasRequiredOptions()
    {
        return (count($this->_argv) >= 4);
    }

    /**
     * @return string
     */
    public function getUsage()
    {
        $usage  = 'Usage: ' . $this->_argv[0] . ' [options]' . PHP_EOL;
        $usage .= ' -d | --database=<database>' . PHP_EOL;
        $usage .= ' -u | --user=<user>' . PHP_EOL;
        $usage .= ' -p | --pass=<password>' . PHP_EOL;
        $usage .= ' -h | --host=<host> (optional)' . PHP_EOL;
        $usage .= ' -t | --port=<port> (optional)' . PHP_EOL;
        $usage .= ' -n | --name=<migration classname> (optional) [default: InitialMigration]' . PHP_EOL;
        $usage .= ' -s | --skip=<tables to be skipped> (optional) [default: phinxlog; comma separated]' . PHP_EOL;
        $usage .= ' -f | --format=<format of file prefix> (optional) [default: "YmdHis"; php date format; result in YYYYMMDDHHMMSS)' . PHP_EOL;
        $usage .= ' -o | --output=<output file> (optional) [default: {format_prefix}_initial_migration.php]' . PHP_EOL;
        $usage .= PHP_EOL;
        $usage .= 'Example: ' . PHP_EOL;
        $usage .= '$ php ' . $this->_argv[0] . ' -d="mydatabase" -u="username" -p="secure-password" -h"=1.2.3.4" ';
        $usage .= '-t="3307" -n="InitialMigration" -f="YmdHis" -o="initial_migration.php"' . PHP_EOL;
        $usage .= PHP_EOL;

        return $usage;
    }

    /**
     * @return array
     */
    public function getOpt()
    {
        return getopt($this->_shortOpts, $this->_longOpts);
    }

}

/**
 * Class Console2Config
 *
 * @package C0DE8\MySQLPhinx
 * @author  Bjoern Ellebrecht (C0DE8)
 */
class Console2Config
{

    /**
     * @var string
     */
    const DEFAULT_HOST = 'localhost';

    /**
     * @var int
     */
    const DEFAULT_PORT = 3306;

    /**
     * @var string
     */
    const DEFAULT_CLASSNAME = 'InitialMigriation';

    /**
     * @var string
     */
    const DEFAULT_SKIP = 'phinxlog';

    /**
     * @ var string
     */
    const DEFAULT_FORMAT_PREFIX = 'YmdHis';

    /**
     * @var string
     */
    const DEFAULT_OUTPUT = 'initial_migration.php';

    /**
     * @var array
     */
    protected static $_mapping = array(
        'd' => 'dbname',
        'u' => 'user',
        'p' => 'pass',
        'h' => 'host',
        't' => 'port',
        'n' => 'classname',
        's' => 'skip',
        'f' => 'format',
        'o' => 'output'
    );

    /**
     * @var array
     */
    protected static $_config = array(
        'dbname'    => '',
        'user'      => '',
        'pass'      => '',
        'host'      => self::DEFAULT_HOST,
        'port'      => self::DEFAULT_PORT,
        'classname' => self::DEFAULT_CLASSNAME,
        'skip'      => array(self::DEFAULT_SKIP),
        'format'    => self::DEFAULT_FORMAT_PREFIX,
        'output'    => self::DEFAULT_OUTPUT
    );


    /**
     * @param array $options
     * @return array
     */
    public static function getConfig(array $options)
    {
        foreach (self::$_mapping as $key => $configKey) {
            if (isset($options[$key]) && !empty($options[$key])) {
                switch($configKey) {
                    case 'skip':
                        $options[$key] = explode(',', $options[$key]);
                        break;
                }
                self::$_config[$configKey] = $options[$key];
            }
        }

        return self::$_config;
    }

}

/**
 * Class MySQL2PhinxInitializer
 *
 * @package C0DE8\MySQLPhinx
 * @author  Bjoern Ellebrecht (C0DE8)
 */
class MySQL2PhinxInitializer
{

    /**
     * @var string
     */
    const SHOW_TABLES = 'SHOW TABLES;';

    /**
     * @var string
     */
    const SHOW_CREATE_TABLE = 'SHOW CREATE TABLE ';


    /**
     * @var mysqli
     */
    protected $_mysqlResource;

    /**
     * @var array
     */
    protected $_config = array();


    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    /**
     * 
     * @return array
     */
    public function getTables()
    {
        if (!($result = $this->_getConnection()->query(self::SHOW_TABLES))) {
            throw new \RuntimeException(
                'ERROR: unable to execute "show table;" on mysql server '
                . $this->_getMySQLNativeError()
            );
        }
        return array_map(function($a) { return $a[0]; }, $result->fetch_all());
    }

    /**
     * @param $table
     * @return string
     */
    public function getCreateTable($table)
    {
        if (!($res = $this->_mysqlResource->query(self::SHOW_CREATE_TABLE . '`' . $table . '`'))) {
            throw new \RuntimeException(
                'ERROR: unable to get create table form mysql server '
                . $this->_getMySQLNativeError()
            );
        }

        $resArr = $res->fetch_assoc();
        return $resArr['Create Table'];
    }


    /**
     * @return mysqli
     */
    protected function _getConnection()
    {
        if (null === $this->_mysqlResource) {
            if (!($this->_mysqlResource = \mysqli_init())) {
                throw new \RuntimeException(
                    'ERROR: unable to initialize mysqli object'
                );
            }

            if(!($this->_mysqlResource->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10))) {
                throw new \RuntimeException(
                    'ERROR: unable to set mysqli option (connection timeout)'
                );
            }
            $result = $this->_mysqlResource->real_connect(
                $this->_config['host'],
                $this->_config['user'],
                $this->_config['pass'],
                $this->_config['dbname'],
                $this->_config['port']
            );

            if (!$result) {
                throw new \RuntimeException(
                    'ERROR: unable to connect to mysql server '
                    . $this->_getMySQLNativeError()
                );
            }
        }

        return $this->_mysqlResource;
    }

    /**
     * return string
     */
    protected function _getMySQLNativeError()
    {
        return '('.mysqli_connect_errno().': '.mysqli_connect_error().')';
    }

}

/**
 * Class Template
 *
 * @package C0DE8\MySQLPhinx
 * @author  Bjoern Ellebrecht (C0DE8)
 */
class Template
{

    public function getPerTable($tableName, $createTableString)
    {
        return <<<CODE

        // Drop table if it already exists
         \$this->execute("DROP TABLE IF EXISTS `$tableName`;");

        // CREATE table string for table: "$tableName"
        \$this->execute("
            $createTableString
        ");

CODE;
    }


    public function getComplete($initialMigrationClassName, $initialMigrationPhpCode)
    {
        return <<<TMPL
<?php
use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class $initialMigrationClassName extends AbstractMigration
{
    public function up()
    {
        // disable foreign key checks to ensure all tables can be initially created
        \$this->execute('SET FOREIGN_KEY_CHECKS=0;');
$initialMigrationPhpCode
        // re-arm foreign key checks
        \$this->execute('SET FOREIGN_KEY_CHECKS=1;');
    }
}

TMPL;
    }

}

/**
 * Class OutputBuilder
 *
 * @package C0DE8\MySQLPhinx
 * @author  Bjoern Ellebrecht (C0DE8)
 */
class OutputBuilder
{

    /**
     * @var array
     */
    protected $_skipTable = array('phinxlog');

    /**
     * @var MySQL2PhinxInitializer
     */
    protected $_initlializer;


    /**
     * OutputBuilder constructor.
     *
     * @param MySQL2PhinxInitializer $initializer
     * @param Template $template
     * @param array|null $skipTable
     */
    public function __construct(
        MySQL2PhinxInitializer $initializer,
        Template $template,
        array $skipTable = null
    ) {
        $this->_initializer = $initializer;
        $this->_template    = $template;
        $this->_skipTable   = $skipTable;
    }

    /**
     * @return string
     */
    public function build()
    {
        $initialMigrationPhpCode = '';

        $tables = $this->_initializer->getTables();

        echo Color::make("...found table: " . count($tables), 'dark_gray') . PHP_EOL;

        foreach($tables as $tableName) {
            echo Color::make("...prosessing table " . $tableName . "...", 'dark_gray');
            if (in_array($tableName, $this->_skipTable)) {
                echo Color::make("skipping!", 'light_gray') . PHP_EOL;
                continue;
            }
            echo Color::make("assembling PHP code...", 'dark_gray');
            $initialMigrationPhpCode .= $this->_template->getPerTable(
                $tableName,
                preg_replace("%\n%", "\n            ", $this->_initializer->getCreateTable($tableName))
            );
            echo Color::make("done.", 'dark_gray') . PHP_EOL;
        }

        return $this->_template->getComplete('initClassName', $initialMigrationPhpCode);
    }

}
// END classes
// #############################################################################


echo PHP_EOL;
echo Color::make("MySQL2PhinxInitializer by Bjoern Ellebrecht", 'green');
echo Color::make(" - version ", 'light_gray') . Color::make(VERSION . ' ' . STATE, 'brown');
echo Color::make(" [".DATE."]", 'dark_gray') . PHP_EOL;
echo Color::make('Phinx MySQL initial migration generator (mysql2phinx-initializer)' . PHP_EOL . PHP_EOL, 'white');

try {

    $console = new Console($argv);

    $console->hasRequiredOptions() or die($console->getUsage());
    echo Color::make("start processing...", 'dark_gray') . PHP_EOL;

    $config  = Console2Config::getConfig($console->getOpt());

    $builder = new OutputBuilder(
        new MySQL2PhinxInitializer(Console2Config::getConfig($console->getOpt())),
        new Template(),
       $config['skip']
    );

    $filename = './' . date($config['format']) . '_' . $config['output'];

    $phpCode = $builder->build();

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // final output to file of generated PHP code for initial phinx migration
    echo Color::make('...writing file "'.$filename.'"...', 'dark_gray');

    file_put_contents($filename, $phpCode);

    echo Color::make('done.', 'dark_gray') . PHP_EOL;
    echo Color::make('done.', 'dark_gray') . PHP_EOL;

    echo PHP_EOL . "\033[0;32m" . 'Operation successful!' . "\033[0m" .PHP_EOL;

} catch (\Exception $e) {
    echo PHP_EOL . "\033[0;31m" . $e->getMessage() . "\033[0m" . PHP_EOL;
}
