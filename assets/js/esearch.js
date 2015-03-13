 $( document ).ready(function(){

 	var contact   = {1:"Home", 2:"Office", 3: "Mobile", 4: "Fax"};
 	var education = {1:"School", 2:"College", 3: "Under Graduation", 4: "Graduation"};
 	var months =
 	{
 		1:"January",
 		2:"February",
 		3:"March",
 		4:"April",
 		5:"May",
 		6:"June",
 		7:"July",
 		8:"August",
 		9:"September",
 		10:"October",
 		11:"November",
 		12:"December"
 	};

 	$('#exampleInputAmount').keypress(function (e) {
 		if (e.which == 13) {
 			$( "#esearch" ).trigger( "click" );
 		}
 	});
 	$("body").on("click", ".show-box", function (e) {
 		$(".modal-user").find(".modal-body").html($(this).html());
 		$(".modal-user").find(".modal-body > .toggle-details").removeClass("hide");
 		$(".modal-user").find(".modal-title").html($(this).find(".title").html());
 		$('.bs-example-modal-lg').modal('show');
 	});

 	$("#esearch").on("click", function (e) {

 		e.preventDefault();

 		var data = $("#exampleInputAmount").val();
 		var p ='';

 		$.post( base_url+"esearch/get" , { q: data } , function( res ) {
						// console.log(res);
						$("#estime" ).html( JSON.parse(res).took / 100 );
						$("#eslimit" ).html( JSON.parse(res).hits );
						$("#escount" ).html( JSON.parse(res).total );
						// $(".dump" ).html( res );return;
						if (JSON.parse(res).total > 0) {
							$.each(JSON.parse(res), function(k, v) {
								if (v.user_id) {
									p += "<div class='show-box col-sm-3' id='"+k+"'>";
									p += "<ul class='list-group list-inline'>";
									p += "<li class='list-group-item'>";
									p += '<img src="'+JSON.parse(v.profile).picture+'" alt="..." class="pull-left img-rounded" height="200" width="200">';
									p += "<div class='title'>"+JSON.parse(v.profile).first_name+" "+JSON.parse(v.profile).last_name+'</div>';
									p += '</li>';
									p += "</ul>";

									p += "<div class='hide toggle-details'>";
									p += '<h4>Work:</h4>';
									p += "<ul class='list-group'>"
									for (var i = 0; i < JSON.parse(v.work).length; i++) {
										p += "<li class='list-group-item'>";
										p += "Company: " +JSON.parse(v.work)[i].organization+"<br/>";
										p += "Position: " +JSON.parse(v.work)[i].position+"<br/>";
										p += JSON.parse(v.work)[i].start_year+" - "+months[parseInt(JSON.parse(v.work)[i].start_month)]+' To ';
										p += JSON.parse(v.work)[i].end_year+" - "+months[parseInt(JSON.parse(v.work)[i].end_month)]+"<br/>";
										p += JSON.parse(v.work)[i].location;
										p += "</li>";
									};
									p += "</ul>";

									p += '<h4>Education:</h4>';
									p += "<ul class='list-group'>";
									for (var i = 0; i < JSON.parse(v.education).length; i++) {
										p += "<li class='list-group-item'>";
										p += JSON.parse(v.education)[i].degree+", ";
										p += JSON.parse(v.education)[i].course+" ";
										p += JSON.parse(v.education)[i].end_date+"<br/>";
										p += education[parseInt(JSON.parse(v.education)[i].type)]+"<br/>";
										p += JSON.parse(v.education)[i].name;
										p += "</li>";
									};
									p += "</ul>";

									p += '<h4>Contact:</h4>';
									p += "<ul class='list-group'>";
									for (var i = 0; i < JSON.parse(v.contact).length; i++) {
										p += "<li class='list-group-item'>";
										p += contact[JSON.parse(v.contact)[i].type]+": ";
										p += JSON.parse(v.contact)[i].number;
										p += "</li>";
									};
									p += "</ul>";


									p += '<h4>Address:</h4>';
									p += "<ul class='list-group'>";
									for (var i = 0; i < JSON.parse(v.address).length; i++) {
										p += "<li class='list-group-item'>";
										p += JSON.parse(v.address)[i].address+",<br/>";
										p += JSON.parse(v.address)[i].city+", ";
										p += JSON.parse(v.address)[i].state+", ";
										p += JSON.parse(v.address)[i].pincode+", <br>";
										p += JSON.parse(v.address)[i].country;
										p += "</li>";
									};
									p += "</ul>";

									p += "</div>";
									p += "</div>";
								};
							});
} else {
	p = "No records";
}
$( ".result" ).html( p );
});
});
});
