<div class="pollin-area"><?php

global $colors, $color_index, $wpdb;
 
$colors = array('bf1a1d', 'bfa024', '830088', '3e4cb9', '52803b', '805b6f', '827f71', '4bc0e0', 'c12a8f', '000000');
$color_index = 0;

// Basic check to make sure the user don't vote again and again.
$voted_for_polls = array();
if(isset($_COOKIE['pollin_voted'])) $voted_for_polls = explode(';', $_COOKIE['pollin_voted']);

if(isset($_REQUEST['action']) and $_REQUEST['action']) { // Poll Reuslts.
	$user_answer = $_REQUEST['answer'];
	if($_REQUEST['action'] != 'show_result' and isset($_REQUEST['answer']) and $_REQUEST['answer']) { //Register a vote
		if(!in_array($question_id, $voted_for_polls)) {
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}pollin_answer SET votes=votes+1 WHERE ID=%d", $user_answer));
			@setcookie('pollin_voted', implode(';', $voted_for_polls) . ";$_REQUEST[question_id]", time() + 60*60*24*365, '/');
			e("Thank you for your vote.");
		} else {
			e("You have already voted for this poll. Multiple votes are not allowed.");
		}
		print "<br />";
	}
	
	//Show result.
	$answers = $wpdb->get_results($wpdb->prepare("SELECT ID, answer, votes FROM {$wpdb->prefix}pollin_answer WHERE question_ID=%d ORDER BY sort_order", $question_id));
	?><table class="pollin-results"><?php
	//First find the total number of votes
	$total = 0;
	foreach($answers as $ans) $total += $ans->votes;
	
	// Show each answer with the number of votes it recived.
	foreach($answers as $ans) {
		print "<tr><td>";
		if(isset($user_answer) and $ans->ID == $user_answer) print "<strong>" . stripslashes($ans->answer) . "</strong>"; //Users answer.
		else print stripslashes($ans->answer);
		print "</td>";
		
		$percent = intval(($ans->votes / $total) * 100);
		$color = pollin_nextColor();
		print "<td class='pollin-result-bar-holder' style='width:200px;'><div class='pollin-result-bar' style='background-color:$color; width:$percent%;'>&nbsp;</div></td>";
		print "<td>{$ans->votes} " . t("Votes") . "($percent%)</td>";
		print "</tr>";
	}
	?>
	</table><br /><br />
	<?php
	print "<strong>" . t("Total Votes") . ": $total</strong>";
	
} else { // Show The poll.
	$question = $wpdb->get_row($wpdb->prepare("SELECT ID, question FROM {$wpdb->prefix}pollin_question WHERE ID=%d AND status='1'", $question_id));
	if($question) { ?>
<script type="text/javascript" src="<?php echo $wpframe_home?>/wp-includes/js/jquery/jquery.js"></script>
<script type="text/javascript">
function init() {
	jQuery("#poll-form").submit(function(e) {
		var answered = false;
	
		jQuery("#poll-form .answer").each(function(i) {
			if(this.checked) {
				answered = true;
				return true;
			}
		});
		if(!answered) {
			alert("<?php e("Please choose an answer") ?>");
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	});
}
jQuery(document).ready(init);
</script>

<form action="" method="post" class="poll-form" id="poll-form" style="text-align:left;">
<?php
echo "<div class='question-content'>" . stripslashes($question->question) . "</div><br />";
echo "<input type='hidden' name='question_id[]' value='{$question->ID}' />";
$dans = $wpdb->get_results("SELECT ID,answer FROM {$wpdb->prefix}pollin_answer WHERE question_ID={$question->ID} ORDER BY sort_order");
foreach ($dans as $ans) {
	echo "<input type='radio' name='answer' id='answer-id-{$ans->ID}' class='answer' value='{$ans->ID}' />";
	echo "<label for='answer-id-{$ans->ID}'>" . stripslashes($ans->answer). "</label><br />";
}

?><br />

<?php if(!in_array($question_id, $voted_for_polls)) { ?>
<input type="submit" name="action" id="action-button" value="<?php e("Vote") ?>"  />
<?php } else { e("You have already placed your vote"); } ?><br />

<input type="hidden" name="question_id" value="<?= $question_id ?>" />
</form>

<?php } else { 
		print t("This poll is closed.") . "<br />";
		print "<a href='?action=show_result'>" . t("Show Results") . "</a>";
	}
}
?>
</div>
