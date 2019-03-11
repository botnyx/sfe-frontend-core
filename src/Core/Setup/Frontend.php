<?php



namespace Botnyx\Sfe\Frontend\Core\Setup;



use Composer\Script\Event;
use Composer\Installer\PackageEvent;


class Frontend {

	function __construct( $vendorDir){
		$this->vendorDir = $vendorDir;
		$this->projectDir = realpath($vendorDir . '/..');

		$this->installedComponents = json_decode(\Botnyx\Sfe\Backend\Core\Setup\Backend::readfile($vendorDir."/composer/installed.json"));

	}


	static function postUpdate(Event $event){

		$vendorDir = realpath($event->getComposer()->getConfig()->get('vendor-dir'));

		#$installer = new \Botnyx\Sfe\Backend\Core\Setup\Backend($vendorDir);
		#$installer->update();

	}

	static function postInstall(Event $event){

		$vendorDir = realpath($event->getComposer()->getConfig()->get('vendor-dir'));


		$installer = new \Botnyx\Sfe\Frontend\Core\Setup\Frontend($vendorDir);
	//	$installer->setup();


	}

	static function readfile ($filename){
		$handle = fopen($filename, "rb");
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		return $contents;
	}



	public function update (){
		echo "\n";
		#echo "Updating: `".$this->installedComponents[0]->name."`\n";
		#echo "version: `".$this->installedComponents[0]->version."`\n";
		//$installed[0]->version_normalized;

		#var_dump("xyz");
		#echo "-----------------------------------------\n";
		#echo "\n\nUNFINISHED!!\n\n";

	}

	/*
		This is the main database setup.
	*/
	public function setup (){
		echo "\n";
		echo "Setting up: `".$this->installedComponents[0]->name."`\n";
		echo "version: `".$this->installedComponents[0]->version."`\n";
		//$installed[0]->version_normalized;




		#var_dump("xyz");
		echo "-----------------------------------------\n";

		$searchDir = realpath($this->vendorDir . '/../..');
		if( file_exists($searchDir."/configuration.ini") ){
			echo "found a previous `configuration.ini` ( ".$searchDir."/configuration.ini"." ) \n";

			$app = $this->readConfiguration($searchDir."/configuration.ini" );
			$coreComponent = $this->getComponentVersion($this->installedComponents);

			#echo "\nsettings\n";
			#print_r($app->settings['conn']);


			/* create the $this->pdo instance */
			$this->createPDO($app->settings['conn']);


			#echo "\npaths\n";
			#print_r($app->paths);
			#echo "\ncoreComponent\n";
			#print_r($coreComponent);

			#echo "need to add config-parse code here..\n";
			////////////////////////////////////////////////////
			$this->createdb($this->pdo);

			//echo "\n\nFINISHED!!\n\n";

		}else{
			echo "\n No configuration found, starting setup.\n";
			$public_html = $this->public_html();
			$tempFolder = $this->tempFolder();

			/* create the $this->pdo instance from requested credentials */
			$dbCredentials = $this->dbCredentials();

			////////////////////////////////////////////////////
			$this->pdo;
			echo "\n\nUNFINISHED!!\n\n";



		}
		echo "\n";
	}


	private function createdb($pdo){
    die("XxXxXxXxXxXxXxXxXxX!");
		// verify db doenst exist
		// createdb from sql
		$setup = new \Botnyx\Sfe\Backend\Core\Setup\Database($pdo,$this->dbname);
		try{
			$setup->create();
		}catch(\Exception $e){
			echo $e->getMessage()."\n";
		}
		$this->updatedb($pdo);
	}

	private function updatedb($pdo){

		$setup = new \Botnyx\Sfe\Backend\Core\Setup\Database($pdo,$this->dbname);

		try{
			$version = $setup->getVersion();
		}catch(\Exception $e){
			echo $e->getMessage()."\n";
		}
		print_r("Currentversion: ".$version['version']);

		$updates = $setup->update($this->vendorDir,$version['version'] );

		//print_r($updates);
		// get dbversion.
		// get list of patches.
		// patch db from dbversion and up.
	}

	private function getComponentVersion($installedComponents){
		foreach($installedComponents as $c){
			if($c->name=="botnyx/sfe-backend-core"){
				return (object)array("name"=>$c->name,
							 "version"=>$c->version,
							 "version_normalized"=>$c->version_normalized
							);
			}
		}

	}

	private function readConfiguration($configfile){
		$app = new \Botnyx\Sfe\Shared\Application(parse_ini_file($configfile, true));
		return $app;
	}

	private function dbCredentials(){
		echo "---------------------\n";
		echo "Configuration step 3.\n";
		echo "Please enter the connection dsn  (mysql:host=localhost;dbname=backend)\n";
		$dsn = rtrim(fgets(STDIN));

		echo "Please enter the database username \n";
		$user = rtrim(fgets(STDIN));
		echo "Please enter the database password \n";
		$pass = rtrim(fgets(STDIN));

		try{
			$this->createPDO(array("dsn"=>$dsn,"user"=>$user,"pass"=>$pass) ) ;
		}catch(\Exception $e){
			echo "\nERROR! - ".$e->getMessage();
			return $this->dbCredentials();
		}

		return array("dsn"=>$dsn,"user"=>$user,"pass"=>$pass);
	}

	private function public_html(){
		echo "---------------------\n";
		echo "Configuration step 1.\n";
		echo "Please enter the location of your web-facing directory \n";
		$input = rtrim(fgets(STDIN));

		if( !file_exists( $input )){
			echo "Error: this location doesnt exist. try again.\n\n\n";
			$this->public_html();
		}
		return $input;
	}

	private function tempFolder(){
		echo "---------------------\n";
		echo "Configuration step 2.\n";
		echo "Please enter the location of your /tmp directory \n";
		$input = rtrim(fgets(STDIN));

		if( !file_exists( $input )){
			echo "Error: this location doesnt exist. try again.\n\n\n";
			$this->tempFolder();
		}
		return $input;
	}

	private function createPDO($c){
		#$c['dsn']="mysql:host=localhost;dbname=backendtest";


		// get db from dsn.
		$dsnp = explode(";",$c['dsn']);
		foreach($dsnp as $y){
			parse_str($y,$x);
			if(key($x)=="dbname"){
				$dbname=$x['dbname'];
				break;
			}
		}

		echo "Using database:". $dbname."\n";
		$this->dbname = $dbname;



		$dboptions = array(
			\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			\PDO::ATTR_EMULATE_PREPARES   => false,
		);
		/*  make db connection.  */
		try{
			//$pdo = new PDO($ini['database']['pdodsn']  );

			$pdo = new \PDO($c['dsn'], $c['dbuser'],$c['dbpassword'],$dboptions );
			// set the default schema for the oauthserver.
			//$result = $pdo->exec('SET search_path TO oauth2'); # POSTGRESQL Schema support
		}catch(\Exception $e){
			throw new \Exception($e->getMessage());
		}
		$this->pdo = $pdo;
	}
}
