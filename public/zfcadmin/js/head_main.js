$(function() {
	
	$('.remove-entry').click(function(){
		var url = $(this).attr('href');
		if(confirm('Supprimer cette entrée ?')){
			$(location).attr('href',url);
		}
		return false;
	});
	
	$('.date').datepicker({
        dateFormat : 'dd/mm/yy'
    });
    
    $(document).ready(function ($) {
    	$('#tabs').tab();
    });
	
	/**************************** Quiz options - hide label delete */
	//$('.delete-button').prev().hide();
	
    
    /**************************** Users restriction date */
    ckeckBirthDate('#identity .date-birth');
    
    
    /**************************** Export */
   	/**** Birth year restriction */
    ckeckBirthDate('#statistics-export .date-birth');
    
    /**** Nb participations */
    $('#statistics-export .date-export').datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat : 'dd/mm/yy',
	});
	
	/**** Layouts */
	$('#zipcode-input').prop('maxLength', 5);
    exportChangeSelect('#lastname-select','#lastname-input', 'Nom');
    exportChangeSelect('#firstname-select', '#firstname-input', 'Prénom');
    exportChangeRadio('.memberid-radio', '#memberid-input');
    exportChangeSelect('#email-select', '#email-input', 'Email');
    exportChangeSelect('#zipcode-select', '#zipcode-input', 'Code postal');
    exportChangeRadio('.inscription-radio', '#inscriptionstart-input, #inscriptionend-input');
    exportChangeRadio('.hardbounce-radio', '#hardbouncestart-input, #hardbounceend-input');
    
    $('.birthdate-radio').change(function() {
		v =  $('.birthdate-radio:checked').val();
		t =  $('.birthdate-radio:checked').parent().text().toLowerCase();
		if (v == 'all'){
			$('#birthdatestart-input, #birthdateend-input, #birthdateequal-input').prop('disabled', true);
			$('#birthdateequal-input, #birthdatestart-input, #birthdateend-input').removeClass('required');
		} else if (v == 'between') {
        	$('#birthdatestart-input, #birthdateend-input').prop('disabled', false);
			$('#birthdateequal-input').removeClass('required');
			$('#birthdateequal-input').prop('disabled', true);			
			$('#birthdatestart-input, #birthdateend-input').addClass('required');
        } else if (v == 'equal') {
        	$('#birthdateequal-input').prop('disabled', false);
			$('#birthdateequal-input').addClass('required');
        	$('#birthdatestart-input, #birthdateend-input').prop('disabled', true);			
			$('#birthdatestart-input, #birthdateend-input').removeClass('required');
        }
    }).trigger('change');
    
    $('.nbpart').change(function() {
		v =  $('.nbpart:checked').val();
		t =  $('.nbpart:checked').parent().text().toLowerCase();
		if (v == 'all'){
			$('#nbpartmin-input, #nbpartmax-input, #nbpartstart-input, #nbpartend-input').prop('disabled', true);
			$('#nbpartmin-input, #nbpartmax-input, #nbpartstart-input, #nbpartend-input').removeClass('required');
		} else if (v == 'betweennb') {
        	$('#nbpartmin-input, #nbpartmax-input').prop('disabled', false);
        	$('#nbpartmin-input, #nbpartmax-input').addClass('required');
			$('#nbpartstart-input, #nbpartend-input').prop('disabled', true);
			$('#nbpartstart-input, #nbpartend-input').removeClass('required');
        } else if (v == 'between') {
			$('#nbpartstart-input, #nbpartend-input').prop('disabled', false);
			$('#nbpartstart-input, #nbpartend-input').addClass('required');
			$('#nbpartmin-input, #nbpartmax-input').prop('disabled', true);
			$('#nbpartmin-input, #nbpartmax-input').removeClass('required');
        }
    }).trigger('change');
    
    /**** Conditions */
   $('.validate').validate();

   $(document).ready(function ($) {
   	$('#load-statistic-share').click(function(e) {
   		e.preventDefault();
   		var self = jQuery(this);   		
   		jQuery.get(this.href, function(data) {
   			jQuery('#statistic-share').parent().append(data);
   			self.remove();
   		}, 'html');
   	});
   	$('#load-statistic-achievement').click(function(e) {
   		e.preventDefault();
   		var self = jQuery(this);
   		jQuery.get(this.href, function(data) {
   			self.parent().append(data);
   			self.remove();
   		}, 'html');
   	});   	
   });
   
});

function ckeckBirthDate(datebirth) {
	var d = new Date();
	var month = d.getMonth()+1;
	var day = d.getDate();
	
	var currentmonth = ((''+month).length<2 ? '0' : '') + month;
	var currentday = ((''+day).length<2 ? '0' : '') + day;
	var currentyear = d.getFullYear();
	
	var currentdate = d.getFullYear() + '/' + ((''+month).length<2 ? '0' : '') + month + '/' + ((''+day).length<2 ? '0' : '') + day;
	
	$(document).ready(function ($) {
		$(datebirth).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat : 'dd/mm/yy',
			yearRange: (currentyear-90)+ ":" + (currentyear-18),
			maxDate: new Date(currentyear-18, currentmonth-1, currentday),
		});
	})
}

function exportChangeSelect (selector, inputtext, inputplaceholder) {
	$(selector).change(function() {
		v =  $(selector+' option:selected').val();
		t =  $(selector+' option:selected').text().toLowerCase();
		if(v == 'all'){
			$(inputtext).attr('placeholder', inputplaceholder);
			$(inputtext).prop('disabled', true);
			$(inputtext).removeClass('required');
		} else {
        	$(inputtext).attr('placeholder', inputplaceholder+' '+t);
        	$(inputtext).prop('disabled', false);
        	$(inputtext).addClass('required');
        }
    }).trigger('change');
}

function exportChangeRadio (selector, inputtext) {
	$(selector).change(function() {
		v =  $(selector+':checked').val();
		t =  $(selector+':checked').parent().text().toLowerCase();
		if((v == 'between') || (v == 'equal')){
			$(inputtext).prop('disabled', false);
        	$(inputtext).addClass('required');
		} else {
        	$(inputtext).prop('disabled', true);
			$(inputtext).removeClass('required');
        }
    }).trigger('change');
}

// TODO update position with one post, not with a 'each'
  $(function() {	
	   $("#sort-table").sortable({
      update: function(event, ui) {
      	
  		var i = 1;
  		
  		$.each($(this).find('tr'),function(){
  			var url = $(this).attr('data-url');
  			var active = $(this).attr('data-active');
  			
  			$.post(
			    url,
    	    	{isActive:active,position:i},
    	    	function(returnVal) {
    	    	    // TODO
    	    	}
	    	 );
  			
  			i++;
  		});

      }
   });
  $( ".sort-table" ).disableSelection();

	    
  });