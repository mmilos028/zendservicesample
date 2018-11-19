<?php
require_once HELPERS_DIR . DS . 'CursorToArrayHelper.php';
require_once HELPERS_DIR . DS . 'ErrorHelper.php';
class GameModel{
	public function __construct(){
	}
	
	public function getOrderedGames($affiliate_name){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			//$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$LIST_GAMES_FOR_ORDER(:P_LIST_GAMES_OUT)');
			$stmt = $dbAdapter->prepare('CALL GAME_ORDER_MANAGEMENT.LIST_GAME_ORDER(:P_AFFILIATE_ID, :P_AFFILIATE_NAME, :p_session_id_in, :c_list_games_with_order)');
			
			// Nemam id affiliate-a vec idem po imenu affiliate-a.
			$affiliate_id = null;
			$stmt->bindParam(':P_AFFILIATE_ID', $affiliate_id);
			
			$stmt->bindParam(':P_AFFILIATE_NAME', $affiliate_name);
			
			// Jos uvek nemam sesiju
			$session_id = -1;
			$stmt->bindParam(':p_session_id_in', $session_id);
			
			$cursorListGames = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':c_list_games_with_order', $cursorListGames);
			
			$stmt->execute(null, false);
			$cursorListGames->execute();
			
			$gamesArr = array("list_games"=>$cursorListGames);
			
			$cursorListGames->free();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
						
			return $gamesArr;
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);

			return array("list_games"=>array());
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex2);
			$errorHelper->serviceError($message, $message);
			return array("list_games"=>array());
		}
	}
	
	/**
	 * Enter description here ...
	 * Returns list of enabled games - does not return exclusive games
	 * possible states are E, null or D, null or E, D
	 * E, null - only enabled games, D,null - only disabled games, E,D - returns all of games
	 * E - enabled, D - disabled, null - not defined
	 * returns terminal type auto or manual login A | M
	 */
	public function getGames($enabled, $disabled, $gctype, $mac_address, $player_id = null){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$LIST_E_D_GAMES(:p_PLAYER_ID_in, :p_status_enabled_in, :p_status_disabled_in, :p_affiliate_in, :p_GCType_in, :p_MAC_address_in, :P_LIST_GAMES_OUT, :P_LIST_POTS_OUT, :p_terminal_type_out, :p_skin_out, :p_MOUSE_out, :p_one_page_out, :p_key_exit_out, :p_enter_pass_out, :p_port_out, :p_general_purpose_out, :p_affiliate_id_out)');
			//$stmt = $dbAdapter->prepare('CALL PLAY_CORE.M$LIST_E_D_GAMES_NEW(:p_PLAYER_ID_in, :p_status_enabled_in, :p_status_disabled_in, :p_affiliate_in, :p_GCType_in, :p_MAC_address_in, :P_LIST_GAMES_OUT, :P_LIST_POTS_OUT, :p_terminal_type_out, :p_skin_out, :p_MOUSE_out, :p_one_page_out, :p_key_exit_out, :p_enter_pass_out, :p_port_out, :p_general_purpose_out, :p_affiliate_id_out)');
			$stmt->bindParam(':p_PLAYER_ID_in', $player_id);
			$stmt->bindParam(':p_status_enabled_in', $enabled);
			$stmt->bindParam(':p_status_disabled_in', $disabled);
			$affiliate_in = null;
			$stmt->bindParam(':p_affiliate_in', $affiliate_in);
			$stmt->bindParam(':p_GCType_in', $gctype);
			$stmt->bindParam(':p_MAC_address_in', $mac_address);
			$cursorListGames = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':P_LIST_GAMES_OUT', $cursorListGames);
			$cursorListPots = new Zend_Db_Cursor_Oracle($dbAdapter);
			$stmt->bindCursor(':P_LIST_POTS_OUT', $cursorListPots);
			$terminal_type_out = "";			
			$stmt->bindParam(':p_terminal_type_out', $terminal_type_out, SQLT_CHR, 255);
			$skin_out = ""; 
			$stmt->bindParam(':p_skin_out', $skin_out, SQLT_CHR, 255);
			$mouse_on_off_out = "OFF";
			$stmt->bindParam(':p_MOUSE_out', $mouse_on_off_out, SQLT_CHR, 255);
			$one_page_out = "1";
			$stmt->bindParam(':p_one_page_out', $one_page_out, SQLT_CHR, 255);
			$key_exit_out = "";
			$stmt->bindParam(':p_key_exit_out', $key_exit_out, SQLT_CHR, 255);
			$enter_pass_out = "";
			$stmt->bindParam(':p_enter_pass_out', $enter_pass_out, SQLT_CHR, 255);
			$port_out = "";
			$stmt->bindParam(':p_port_out', $port_out, SQLT_CHR, 255);
			$general_purpose = "0";
			$stmt->bindParam(':p_general_purpose_out', $general_purpose, SQLT_CHR, 255);
			$affiliate_id_out = "0";
			$stmt->bindParam(':p_affiliate_id_out', $affiliate_id_out, SQLT_CHR, 255);
			$stmt->execute(null, false);
			$dbAdapter->commit();
			$cursorListGames->execute();			
			$cursorListPots->execute();
			$cursorListGames->free();
			$cursorListPots->free();			
			$dbAdapter->closeConnection();
			return array("list_games"=>$cursorListGames, "terminal_type"=>$terminal_type_out, "skin"=>$skin_out, "key_exit"=>$key_exit_out, "enter_password"=>$enter_pass_out, "mouse_on_off"=>$mouse_on_off_out, "one_page"=>$one_page_out, "list_pots"=>$cursorListPots, "port"=>$port_out, "general_purpose"=>$general_purpose, "affiliate_id"=>$affiliate_id_out);
		}catch(Zend_Db_Adapter_Oracle_Exception $ex1){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex1);
			$errorHelper->serviceError($message, $message);
			return array("list_games"=>array());
		}catch(Zend_Exception $ex2){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex2);
			$errorHelper->serviceError($message, $message);
			return array("list_games"=>array());
		}
	}
	//show game move
	public function listGameMove($transaction_id, $session_id, $terminal_name){
	    /* @var $dbAdapter Zend_Db_Adapter_Oracle */
		$dbAdapter = Zend_Registry::get('db_auth');
		$dbAdapter->beginTransaction();
		try{
			$stmt = $dbAdapter->prepare('CALL REPORTS.M$MOVE(:p_transaction_id_in, :p_session_id_in, :p_subject_name_in, :active_lines, :bet, :bonus_games, :credit_amount, :card_to_beat, :decimal_counter, :game_state, :gamble_type, :gamble_level, :gamble_card_history, :gamble_win, :last_win, :reels_position, :total_bet, :total_win, :used_bonus_games, :win, :expanding_symbol_id, :c4bonus_win, :c4bonus, :scavenger_bonus, :min_bet, :bet_level_threshold, :reel0, :reel1, :reel2, :reel3, :reel4, :p_game_id_out)');
			$stmt->bindParam(':p_transaction_id_in', $transaction_id);
			$stmt->bindParam(':p_session_id_in', $session_id);
			$stmt->bindParam(':p_subject_name_in', $terminal_name, SQLT_CHR, 255);
			$active_lines = "";
			$stmt->bindParam(':active_lines', $active_lines, SQLT_CHR, 255);
			$bet = "";
			$stmt->bindParam(':bet', $bet, SQLT_CHR, 255);
			$bonus_games = "";
			$stmt->bindParam(':bonus_games', $bonus_games, SQLT_CHR, 255);
			$credit_amount = "";
			$stmt->bindParam(':credit_amount', $credit_amount, SQLT_CHR, 255);
			$card_to_beat = "";
			$stmt->bindParam(':card_to_beat', $card_to_beat, SQLT_CHR, 255);
			$decimal_counter = "";
			$stmt->bindParam(':decimal_counter', $decimal_counter, SQLT_CHR, 255);
			$game_state = "";
			$stmt->bindParam(':game_state', $game_state, SQLT_CHR, 255);
			$gamble_type = "";
			$stmt->bindParam(':gamble_type', $gamble_type, SQLT_CHR, 255);
			$gamble_level = "";
			$stmt->bindParam(':gamble_level', $gamble_level, SQLT_CHR, 255);
			$gamble_card_history = "";
			$stmt->bindParam(':gamble_card_history', $gamble_card_history, SQLT_CHR, 255);
			$gamble_win = "";
			$stmt->bindParam(':gamble_win', $gamble_win, SQLT_CHR, 255);
			$last_win = "";
			$stmt->bindParam(':last_win', $last_win, SQLT_CHR, 255);
			$reels_position = "";
			$stmt->bindParam(':reels_position', $reels_position, SQLT_CHR, 255);
			$total_bet = "";
			$stmt->bindParam(':total_bet', $total_bet, SQLT_CHR, 255);
			$total_win = "";
			$stmt->bindParam(':total_win', $total_win, SQLT_CHR, 255);
			$used_bonus_games = "";
			$stmt->bindParam(':used_bonus_games', $used_bonus_games, SQLT_CHR, 255);
			$win = "";
			$stmt->bindParam(':win', $win, SQLT_CHR, 255);
			$expanding_symbol_id = "";
			$stmt->bindParam(':expanding_symbol_id', $expanding_symbol_id, SQLT_CHR, 255);
			$c4bonus_win = "";
			$stmt->bindParam(':c4bonus_win', $c4bonus_win, SQLT_CHR, 255);
			$c4bonus = "";
			$stmt->bindParam(':c4bonus', $c4bonus, SQLT_CHR, 255);
			$scavenger_bonus = "";
			$stmt->bindParam(':scavenger_bonus', $scavenger_bonus, SQLT_CHR, 255);
			$min_bet = "";
			$stmt->bindParam(':min_bet', $min_bet, SQLT_CHR, 255);
			$bet_level_threshold = "";
			$stmt->bindParam(':bet_level_threshold', $bet_level_threshold, SQLT_CHR, 255);
			$reel0 = "";
			$stmt->bindParam(':reel0', $reel0, SQLT_CHR, 255);
			$reel1 = "";
			$stmt->bindParam(':reel1', $reel1, SQLT_CHR, 255);
			$reel2 = "";
			$stmt->bindParam(':reel2', $reel2, SQLT_CHR, 255);
			$reel3 = "";
			$stmt->bindParam(':reel3', $reel3, SQLT_CHR, 255);
			$reel4 = "";
			$stmt->bindParam(':reel4', $reel4, SQLT_CHR, 255);
			$game_id = "";
			$stmt->bindParam(':p_game_id_out', $game_id, SQLT_CHR, 255);
			$stmt->execute();
			$dbAdapter->commit();
			$dbAdapter->closeConnection();
			return array("active_lines"=>$active_lines, "bet"=>$bet, "bonus_games"=>$bonus_games, "credit_amount"=>$credit_amount, "card_to_beat"=>$card_to_beat, "decimal_counter"=>$decimal_counter, "game_state"=>$game_state,
					"gamble_type"=>$gamble_type, "gamble_level"=>$gamble_level, "gamble_card_history"=>$gamble_card_history, "gamble_win"=>$gamble_win, "last_win"=>$last_win, "reels_position"=>$reels_position, "total_bet"=>$total_bet, "total_win"=>$total_win,
					"used_bonus_games"=>$used_bonus_games, "win"=>$win, "expanding_symbol_id"=>$expanding_symbol_id, "c4bonus_win"=>$c4bonus_win, "c4bonus"=>$c4bonus, "scavenger_bonus"=>$scavenger_bonus, "min_bet"=>$min_bet, "bet_level_threshold"=>$bet_level_threshold, "reel0"=>$reel0, "reel1"=>$reel1, "reel2"=>$reel2, "reel3"=>$reel3, "reel4"=>$reel4, "game_id"=>$game_id);
		}catch(Zend_Exception $ex){
			$dbAdapter->rollBack();
			$dbAdapter->closeConnection();
			$errorHelper = new ErrorHelper();
			$message = CursorToArrayHelper::getExceptionTraceAsString($ex);
			$errorHelper->serviceError($message, $message);
			throw new Zend_Exception($message);
		}
	}
}

