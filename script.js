var current_question = 1;
var total_questions = 0;

function nextQuestion(e) {
	var answered = false;
	
	jQuery("#question-" + current_question + " .answer").each(function(i) {
		if(this.checked) {
			answered = true;
			return true;
		}
	});
	if(!answered) {
		if(!confirm("You did not select any answer. Are you sure you want to continue?")) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	}
	
	jQuery("#question-" + current_question).hide();
	current_question++;
	jQuery("#question-" + current_question).show();
	
	if(total_questions <= current_question) {
		jQuery("#next-question").hide();
		jQuery("#action-button").show();
	}
}

function init() {
	jQuery("#question-1").show();
	total_questions = jQuery(".question").length;
	
	jQuery("#next-question").click(nextQuestion);
}

jQuery(document).ready(init); 
