// Friends Panels - dotrage - 2010.08.11

function loader(id,view){
	$("#ajax").html("Loading...");
	$("#ajax").load("load.php?m="+m+"&u="+u+"&view="+view,function(){
		$("#tabs-1").removeClass("tabs-1");
		$("#tabs-2").removeClass("tabs-2");
		$("#tabs-3").removeClass("tabs-3");
		$("#tabs-4").removeClass("tabs-4");
		$("#tabs-5").removeClass("tabs-5");
		$("#"+id).addClass(id);								
	});			
}

function single_loader(view){
	$("#ajax").html("Loading...");
	$("#ajax").load("load.php?m="+m+"&u="+u+"&view="+view);	
}

function tag_panel(){
	var url = $("#panel_url").val();
	if (url != ""){
		$.ajax({
			type: "POST",
			url: "tag.php",
			data: { "url" : url, "m" : m, "u" : u },
			success: function(html){
				if (html == "error"){
					alert("You have already tagged this panel or the panel could not tagged.");
				}
				else{
					$("#panel_url").val('');						
					loader("tabs-2","my-panels");
				}
			}
		});	
	}
	else{
		alert("Please enter a valid URL.");
	}
}

function tag_lists(data,tab){
	if (data != ""){
		$.ajax({
			type: "POST",
			url: "tag-lists.php",
			data: data,
			success: function(html){
				if (html == "error"){
					alert("Could not tag all your lists.");
				}
				else{
					if (tab == 5){						
						loader("tabs-5","panels-friends-watch");
					}
					else{
						loader("tabs-3","my-friends-panels");
					}
				}
			}
		});	
	}
	else{
		alert("Invalid Request.");
	}
}

$(document).ready(function(){
	$("#header").click(function(){
		window.location = "http://bcnsocial.com";
	});
	$("#header-right").click(function(){
		window.location = "http://bcnsocial.com";
	});
	$("#header-register").click(function(){
		window.location = "http://www.barcampnashville.org/bcn10/user/register";
	});    	
});		

function save_phone(user_id,phone,mode){
    $.ajax({
        type: "POST",
        url: "save-phone.php",
			data: { "user_id" : user_id, "phone" : phone, "mode" : mode },
			success: function(html){
                if (html == "success" && mode == "save"){    
                    $("#phone-form").hide();
                    $("#phone-result").html("Your mobile number has been saved and we're sending you a confirmation message now.");
                }
                if (html == "success" && mode == "cancel"){    
                    $("#mcancel").hide();
                    $("#phone-result").html("Your mobile number has been removed and SMS notifications have been cancelled.");
                }                
			}
		});	
}