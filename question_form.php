<?php
include('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

if($action == 'edit') {
	$dans = $wpdb->get_results("SELECT answer FROM {$wpdb->prefix}pollin_answer WHERE question_ID=$_REQUEST[question] ORDER BY sort_order");
	$dques= $wpdb->get_row("SELECT question,status FROM {$wpdb->prefix}pollin_question WHERE ID=$_REQUEST[question]");
}

$anscount = 4;
if($action == 'edit' and $anscount < count($dans)) $anscount = count($dans);

?>

<div class="wrap">
<h2><?php echo t(ucfirst($action) . " Poll"); ?></h2>
<input type="hidden" id="title" name="ignore_me" value="This is here for a workaround for a editor bug" />

<?php if($action == 'edit') { ?>
<p><?php e('To add this poll to your blog, insert the code ') ?> [POLLIN <?=$_REQUEST['question'] ?>] <?php e('into any post.') ?></p>
<?php } ?>

<input type="hidden" id="title" name="ignore_me" value="This is here for a workaround for a editor bug" />

<?php
wpframe_add_editor_js();
?>
<script type="text/javascript">
var answer_count = <?php echo $anscount; ?>;

function newAnswer() {
	answer_count++;
	var para = document.createElement("p");
	var textarea = document.createElement("textarea");
	textarea.setAttribute("name", "answer[]");
	textarea.setAttribute("rows", "3");
	textarea.setAttribute("cols", "50");
	para.appendChild(textarea);
	
	document.getElementById("extra-answers").appendChild(para);
}
</script>

<form name="post" action="edit.php?page=pollin/question.php" method="post" id="post">
<div id="poststuff">
<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<div class="postbox">
<h3 class="hndle"><span><?php e('Question') ?></span></h3>
<div class="inside">
<?php the_editor(stripslashes($dques->question)); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Answers') ?></span></h3>
<div class="inside">
<?php for($i=1; $i<=$anscount; $i++) { ?>
<p><textarea name="answer[]" rows="3" cols="50"><?php if($action == 'edit') echo stripslashes($dans[$i-1]->answer); ?></textarea>
<?php } ?>

<div id="extra-answers"></div>

<a href="javascript:newAnswer();"><?php e("Add New Answer"); ?></a>
</div></div>


<div class="postbox">
<h3 class="hndle"><span><?php e('Status') ?></span></h3>
<div class="inside">
<label for="status">Active</label>
<input type="checkbox" name="status" value="1" id="status" <?php if($dques->status or $action=='new') print " checked='checked'"; ?> />
</div></div>

</div>

<p class="submit">
<input type="hidden" name="question" value="<?php echo stripslashes($_REQUEST['question'])?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="action" value="<?php echo $action ?>" /> 
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save') ?>" style="font-weight: bold;" />
</p>
<a href="edit.php?page=pollin/question.php"><?php e("Go to Polls Page") ?></a>
</div>
</form>

</div>
