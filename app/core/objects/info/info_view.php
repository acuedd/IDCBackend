<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 30/07/18
 * Time: 16:42
 */
include_once "core/global_config.php";

class info_view extends global_config implements window_view{

	private static $_instance;
	private $strAction = "";

	public function __construct($arrParams){
		parent::__construct($arrParams);
	}

	public static function getInstance($arrParams){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self($arrParams);
		}
		return self::$_instance;
	}

	public function setStrAction($strAction)
	{
		$this->strAction = $strAction;
	}

	public function draw()
	{
		// TODO: Implement draw() method.
		draw_header();

		theme_draw_centerbox_open("Commands");
		?>
		<div class="text-center">
			<a href="<?php print $this->strAction; ?>&terminal=1" target="phpInfo">Run commands</a><br>
		</div>
		<?php
		theme_draw_centerbox_close();

		theme_draw_centerbox_open("PHP Info");
			?>
			<div class="text-center">
				<a href="<?php print $this->strAction; ?>&pi=1" target="phpInfo">phpInfo</a><br>
			</div>
			<?php
		theme_draw_centerbox_close();

		theme_draw_centerbox_open("Modules");
		reset($this->cfg['modules']);

			$col = 0;
			$maxcol = 3;
			print "<table border=0 align=center width='99%' cellpadding=2 cellspacing=0>\n";
			foreach($this->cfg["modules"] AS $key => $value){
				if( $col == 0 ) print "<tr>\n";
				$strBoldOpen = (check_module($key, false))?"<b>":"";
				$strBoldClose = ($strBoldOpen == "<b>")?"</b>":"";

				print "\t<td width=100 class=\"centerboxtext\">".$strBoldOpen.$key.$strBoldClose."</td>\n";
				$col++;
				if($col > $maxcol) {
					print "</tr>\n";
					$col = 0;
				}
			}

			if ($col > 0) {
				print "<td colspan=".($maxcol-$col+1)." class=\"centerboxtext\">&nbsp;</td>\n";
				print "</tr>\n";
			}
			echo "</table>";
		theme_draw_centerbox_close();


		theme_draw_centerbox_open("Config");
		$arrTMP = $this->config;
		unset($arrTMP["user"]);
		unset($arrTMP["password"]);
		dump($arrTMP);
		theme_draw_centerbox_close();


		theme_draw_centerbox_open("Modular Config");
		dump($this->cfg);
		theme_draw_centerbox_close();

		theme_draw_centerbox_open("Sesion");
		dump($_SESSION["wt"]);
		theme_draw_centerbox_close();

		theme_draw_centerbox_open("PHP Extensions");
		dump(get_loaded_extensions());
		theme_draw_centerbox_close();

		draw_footer();
	}

	public function terminalEmulator($previous_commands  = "")
	{
		draw_header();
		$this->styles();
		?>
		<div class="content">
			<div class="terminal" onclick="document.getElementById('command').focus();" id="terminal">
				<div class="bar">
					<?php echo `whoami`, ' - ', exec($previous_commands . 'pwd'); ?>
				</div>
				<form action="<?php echo $this->strAction; ?>&terminal=1" method="post" class="commands" id="commands">
					<input type="hidden" name="persist_command_id" id="persist_command_id" />
					<?php
                        if ( ! empty($_SESSION["wt"]['commands'])) {
                            ?>
                            <div>
                                <?php
                                    foreach ($_SESSION["wt"]['commands']['commands'] as $index => $command) {
                                        $strVal = "Un-Persist";
                                        if(isset($_SESSION["wt"]['commands']['persist_commands'][$index])){
	                                        if ($_SESSION["wt"]['commands']['persist_commands'][$index]) {
		                                        $strVal = "Persist";
	                                        }
                                        }
                                        ?>
                                        <input type="button" value="<?php print $strVal; ?>" onfocus="this.style.color='#0000FF';" onblur="this.style.color='';" onclick="toggle_persist_command(<?php echo $index; ?>);" class="persist_button" />
                                        <pre><?php echo '$ ', $command, "\n"; ?></pre>
                                        <?php
                                       if(isset($_SESSION["wt"]['commands']['command_responses'])){
                                            foreach ($_SESSION["wt"]['commands']['command_responses'][$index] as $value) {
                                                ?>
                                                <pre><?php echo htmlentities($value), "\n"; ?></pre>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                            </div>
					        <?php
                        }
                        ?>
					$ <?php
                        if ( ! isset($_SESSION["wt"]['commands']['logged_in'])){
                            ?>Password:
						    <input type="password" name="command" id="command" />
					        <?php
                        }
                        else {
                            ?>
                            <input type="text" name="command" id="command" autocomplete="off" onkeydown="return command_keyed_down(event);" />
                            <input type="button" value="Persist" onfocus="this.style.color='#0000FF';" onblur="this.style.color='';" onclick="toggle_persist_command(<?php if (isset($_SESSION['commands'])) { echo count($_SESSION['commands']); } else { echo 0; } ?>);" class="persist_button" />
                            <?php
                        }
                        ?>
				</form>
			</div>
		</div>
        <div class="text-center">
            <form action="<?php echo $this->strAction; ?>&terminal=1" method="post">
                <input type="hidden" name="clear" value="clear" />
                <input type="submit" class="btn" value="Clear" onfocus="this.style.color='#0000FF';" onblur="this.style.color='';" />
            </form>
        </div>

        <script type="text/javascript">
	        <?php
	        $single_quote_cancelled_commands = array();
	        if ( ! empty( $_SESSION["wt"]['commands']['commands'] ) ) {
		        foreach ($_SESSION["wt"]['commands']['commands'] as $command) {
			        $cancelled_command = str_replace('\\', '\\\\', $command);
			        $cancelled_command = str_replace('\'', '\\\'', $command);
			        $single_quote_cancelled_commands[] = $cancelled_command;
		        }
	        }
	        ?>
            var previous_commands = ['', '<?php echo implode('\', \'', $single_quote_cancelled_commands) ?>', ''];

            $(".wrapper").css({ "background-color":"#000000"})

            var current_command_index = previous_commands.length - 1;
            document.getElementById('command').select();

            document.getElementById('terminal').scrollTop = document.getElementById('terminal').scrollHeight;

            function toggle_persist_command(command_id)
            {
                document.getElementById('persist_command_id').value = command_id;
                document.getElementById('commands').submit();
            }
            function command_keyed_down(event)
            {
                var key_code = get_key_code(event);
                if (key_code == 38) { //Up arrow
                    fill_in_previous_command();
                } else if (key_code == 40) { //Down arrow
                    fill_in_next_command();
                } else if (key_code == 9) { //Tab

                } else if (key_code == 13) { //Enter
                    if (event.shiftKey) {
                        toggle_persist_command(<?php
					        if (isset($_SESSION["wt"]['commands']['commands'])) {
						        echo count($_SESSION["wt"]['commands']['commands']);
					        } else {
						        echo 0;
					        }
					        ?>);
                        return false;
                    }
                }
                return true;
            }

            function fill_in_previous_command()
            {
                current_command_index--;
                if (current_command_index < 0) {
                    current_command_index = 0;
                    return;
                }
                document.getElementById('command').value = previous_commands[current_command_index];
            }

            function fill_in_next_command()
            {
                current_command_index++;
                if (current_command_index >= previous_commands.length) {
                    current_command_index = previous_commands.length - 1;
                    return;
                }
                document.getElementById('command').value = previous_commands[current_command_index];
            }

            function get_key_code(event)
            {
                var event_key_code = event.keyCode;
                return event_key_code;
            }
        </script>

		<?php

		draw_footer();
	}

	public function styles()
	{
		?>
		<style type="text/css">
			body {
				background-color: #000000;
				color: #00FF00;
				font-family: monospace;
				font-weight: bold;
				font-size: 12px;
			}
			.wrapper{
				background-color: #000000;
			}
			.content-wrapper{
				background-color: #000000;
			}
			.content{
				background-color: #000000;
			}
			input, textarea {
				color: inherit;
				font-family: inherit;
				font-size: 14px;
				font-weight: bold;
				background-color: inherit;
				border: inherit;
			}
			.content {
				width: 100%;
				min-width: 400px;
				margin: 0 auto;
				text-align: left;
				overflow: auto;
			}
			.terminal {
				border: 1px solid #00FF00;
				height: 600px;
				position: relative;
				overflow: auto;
				padding-bottom: 20px;
			}
			.terminal .bar {
				border-bottom: 1px solid #00FF00;
				padding: 2px;
				white-space: nowrap;
				overflow: hidden;
			}
			.terminal .commands {
				padding: 2px;
				padding-right: 0;
			}
			.terminal #command {
				width: 90%;
			}
			.terminal .colorize {
				color: #0000FF;
			}
			.terminal .persist_button {
				float: right;
				border-width: 1px 0 1px 1px;
				border-style: solid;
				border-color: #00FF00;
				clear: both;
			}

            pre {
                display: block;
                font-family: monospace;
                white-space: pre;
                margin: 0;
                padding: 0;
                font-size: 12px;
                color: inherit;
                background-color: inherit;
                border: 0px solid transparent;
                line-height: inherit;

            }
		</style>
		<?php
	}
}