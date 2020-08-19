<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 30/07/18
 * Time: 16:07
 */
include_once ("core/global_config.php");
include_once "core/objects/info/info_model.php";
include_once "core/objects/info/info_view.php";

class info_controller extends global_config implements window_controller{

	private $boolPrintJson = false;
	private $boolUTF8 = true;
	private $strAction = "";
	public function __construct($arrParams = array()){
		parent::__construct($arrParams);
	}

	public function setStrAction($strAction)
	{
		$this->strAction = $strAction;
	}
	/**
	 * @param bool $boolPrintJson
	 */
	public function setBoolPrintJson( $boolPrintJson)
	{
		$this->boolPrintJson = $boolPrintJson;
	}

	/**
	 * @param bool $boolUTF8
	 */
	public function setBoolUTF8( $boolUTF8)
	{
		$this->boolUTF8 = $boolUTF8;
	}

	public function main()
	{
		if (!check_user_class("admin")) die($this->lang["ACCESS_DENIED"]);

		$view = info_view::getInstance($this->arrParams);
		$view->setStrAction($this->strAction);
		if(isset($this->arrParams["pi"])){
			phpinfo();
			die();
		}
		else if(isset($this->arrParams["pe"])){
			phpexpress();
			die();
		}
		else if(isset($this->arrParams["terminal"])){
			if(isset($this->arrParams["clear"])){
				$this->clear_command();
			}
			$previous_commands = $this->runCommands();
			$view->terminalEmulator($previous_commands);
			die();
		}

		$view->draw();
	}

	public function runCommands()
	{

		if ( !isset($_SESSION["wt"]["commands"]['persist_commands']) OR !isset($_SESSION["wt"]["commands"]['commands'])) {
			$_SESSION["wt"]["commands"]['persist_commands'] = array();
			$_SESSION["wt"]["commands"]['commands'] = array();
			$_SESSION["wt"]["commands"]['command_responses'] = array();
		}
		$toggling_persist = FALSE;
		$toggling_current_persist_command = FALSE;

		if (isset($this->arrParams['persist_command_id']) AND is_numeric($this->arrParams['persist_command_id'])) {
			$toggling_persist = TRUE;
			$persist_command_id = $this->arrParams['persist_command_id'];
			if (count($_SESSION["wt"]["commands"]['persist_commands']) == $persist_command_id) {
				$toggling_current_persist_command = TRUE;
			} else {
				if(isset($_SESSION["wt"]["commands"]['persist_commands'][$persist_command_id])){
					$_SESSION["wt"]["commands"]['persist_commands'][$persist_command_id] =
						! $_SESSION["wt"]["commands"]['persist_commands'][$persist_command_id];
				}
			}
		}

		$previous_commands = '';

		foreach ($_SESSION["wt"]["commands"]['persist_commands'] as $index => $persist) {
			if ($persist) {
				$current_command = "";
				if(isset($_SESSION["wt"]["commands"]['commands'][$index]))
					$current_command = $_SESSION["wt"]["commands"]['commands'][$index];

				if ($current_command != '') {
					$previous_commands .= $current_command . '; ';
				}
			}
		}

		if(isset($this->arrParams["command"])){
		    include_once "modules/users/objects/users/users_model.php";
		    $objM = users_model::getInstance([]);
		    $arrinfoWM = $objM->getAllUserInfo(1);
			$command = $this->arrParams['command'];
			if ( ! isset($_SESSION["wt"]["commands"]['logged_in'])) {
				if (MD5($command) == $arrinfoWM["password"]) {
					$_SESSION["wt"]["commands"]['logged_in'] = TRUE;
					$response = array('Welcome, ' . str_replace("\n", '', `whoami`) . '!!');
				} else {
					$response = array('Incorrect Password');
				}
				array_push($_SESSION["wt"]["commands"]['persist_commands'], FALSE);
				array_push($_SESSION["wt"]["commands"]['commands'], 'Password: ');
				array_push($_SESSION["wt"]["commands"]['command_responses'], $response);
			}
			else {
				if ($command != '' AND ! $toggling_persist) {
					if ($command == 'logout') {
						session_unset();
						$response = array('Successfully Logged Out');
					} elseif ($command == 'clear') {
						clear_command();
					} else {
						exec($previous_commands . $command . ' 2>&1', $response, $error_code);
						if ($error_code > 0 AND $response == array()) {
							$response = array('Error');
						}
					}
				}
				else {
					$response = array();
				}
				if ($command != 'logout' AND $command != 'clear') {
					if ($toggling_persist) {
						if ($toggling_current_persist_command) {
							array_push($_SESSION["wt"]["commands"]['persist_commands'], TRUE);
							array_push($_SESSION["wt"]["commands"]['commands'], $command);
							array_push($_SESSION["wt"]["commands"]['command_responses'], $response);
							if ($command != '') {
								$previous_commands = $previous_commands . $command . '; ';
							}
						}
					}
					else {
						array_push($_SESSION["wt"]["commands"]['persist_commands'], FALSE);
						array_push($_SESSION["wt"]["commands"]['commands'], $command);
						array_push($_SESSION["wt"]["commands"]['command_responses'], $response);
					}
				}
			}
		}

		return $previous_commands;
	}

	public function clear_command()
	{
		if (isset($_SESSION["wt"]['commands']["logged_in"])) {
			$logged_in = TRUE;
		} else {
			$logged_in = FALSE;
		}

		if(isset($_SESSION["wt"]['commands'])){
			unset($_SESSION["wt"]["commands"]);
		}

		if ($logged_in) {
			$_SESSION["wt"]['commands']['logged_in'] = TRUE;
		}
	}


}