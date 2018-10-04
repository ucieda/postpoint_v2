$(document).ready(function () {

	$(".nav-item-toggle").on("click",function(){
		$(".nav-item-toggle").removeClass('nav-active');
		$(this).addClass('nav-active');

		if($(this).attr("tabindex")==1){
			$(".head-text").html("Please, fill all fields to Log in");
			$("#con-item-2").hide();
			$("#con-item-1").show();
		}
		if($(this).attr("tabindex")==2){
			$(".head-text").html("Please, fill personal details to add account");
			$("#con-item-1").hide();
			$("#con-item-2").show();
		}

	});

	$('.nav-item').click(function() {
        $('.nav-item').removeClass('nav-active');
        $(this).addClass('nav-active');
    });

	$("body").on("click","#menu-bar-btn",function(){
		$("#side-nav").css("left","0px");
	});

	$("#close-sidenav").on("click",function(){
		$("#side-nav").css("left","-200px");
	});

	$(window).on("click",function(e){
		if($(e.target).attr("id")!="menu-bar-btn" &&  $(e.target).attr("id")!="close-sidenav" &&  !$(e.target).closest('#side-nav').length){
			$("#side-nav").css("left","-200px");
		}
	});




	  $(".set > a").on("click", function(){
        if($(this).hasClass('active')){
            $(this).removeClass("active");
            $(this).siblings('.content').slideUp(200);
            $(".set > a i").removeClass("fa-chevron-up").addClass("fa-chevron-down");
        }else{
            $(".set > a i").removeClass("fa-chevron-up").addClass("fa-chevron-down");
            $(this).find("i").removeClass("fa-chevron-down").addClass("fa-chevron-up");
            $(".set > a").removeClass("active");
            $(this).addClass("active");
            $('.content').slideUp(200);
            $(this).siblings('.content').slideDown(200);
        }

    });


//	$(window).resize(function(){
//
//		if(parseInt($(window).width()) > 991){
//			$("#main_content").css("height",(parseInt($(window).height()) - 190) + "px");
//			$("#nav-aside").css("height",(parseInt($(window).height()) - 160));
//		}
//		else{
//			$("#main_content").css("height",(parseInt($(window).height()) - 110) + "px");
//		}
//
//	});




	// ================================    Accounts Delete user      =============================
		$("#delete-user-btn-gray").on("click",function(){
			$(".select").css({
				opacity : '1',
				paddingTop :'30px'

			});

			$(".mail").css({
				opacity : '0',
				marginTop : '0'
			});

			$("#log_in :input").attr("disabled", true);
			$("#log_in").css({
				opacity : '0.6'

			});


			$("#delete-user-btn-gray").hide();
			$("#export_email").hide();

			$("#cancel-delete").show();
			$("#delete-user-btn-red").show();

		});



		$(".check-user").on("click",function(){
			if($("#cancel-delete").is(":visible")==true){
				if($(this).find("input").prop("checked")==true){
					$(this).find("input").prop("checked",false);
					$(this).css("border-bottom","none");
				}
				else{
					$(this).find("input").prop("checked",true);
					$(this).css("border-bottom","5px solid #eb6361");
				}
			}

		});





		$("#cancel-delete").on("click",function(){
			$(".select").css({
				opacity : '0',
				paddingTop :'0px'

			});

			$(".mail").css({
				opacity : '1',
				marginTop : '20px'
			});

			$("#log_in :input").attr("enabled", true);
			$("#log_in").css({
				opacity : '1'

			});


			$("#delete-user-btn-gray").show();
			$("#export_email").show();

			$("#cancel-delete").hide();
			$("#delete-user-btn-red").hide();

			$(".check-user").css("border-bottom","none");
			$(".check-user").find("input").prop("checked",false);
			$("#log_in :input").attr("disabled", false);

		});
	// ================================  End    Accounts Delete user      =============================






	// ==============  Publishing     ================================


	$("body").on("click",".tab-acc",function () {


		if($(".resp-tab-active").attr("tabindex")==2){
			$("#cancel-delete-pos").hide();
			$("#delete-post-acces").show();
			$("#delete-post-sel").hide();
			$("#clear-log-btn").hide();
		}

		else if($(".resp-tab-active").attr("tabindex")==3){
			$("#cancel-delete-pos").hide();
			$("#delete-post-acces").hide();
			$("#delete-post-sel").hide();
			$("#clear-log-btn").show();
		}

		else{
			$("#cancel-delete-pos").hide();
			$("#delete-post-acces").hide();
			$("#delete-post-sel").hide();
			$("#clear-log-btn").hide();
		}


	});


	$(window).resize(function(){

		if($(".resp-tab-active").attr("tabindex")==2){
			$("#cancel-delete-pos").hide();
			$("#delete-post-acces").show();
			$("#delete-post-sel").hide();
			$("#clear-log-btn").hide();
		}

		else if($(".resp-tab-active").attr("tabindex")==3){
			$("#cancel-delete-pos").hide();
			$("#delete-post-acces").hide();
			$("#delete-post-sel").hide();
			$("#clear-log-btn").show();
		}

		else{
			$("#cancel-delete-pos").hide();
			$("#delete-post-acces").hide();
			$("#delete-post-sel").hide();
			$("#clear-log-btn").hide();
		}

	});


	$("#delete-post-acces").on("click",function () {
		$("#delete-post-acces").hide();
		$("#delete-post-sel").show();
		$("#cancel-del-pos").show();

		$("#checked_table").show();
		$("#simple_table").hide();
		
	});

	$("#cancel-del-pos").on("click",function () {

		$("#delete-post-acces").show();
		$("#delete-post-sel").hide();
		$("#cancel-del-pos").hide();

		$("#checked_table").find("input").prop("checked",false);
		$("#simple_table").show();
		$("#checked_table").hide();

		
	});


			//======================  calendar   ======================
				$("#open-calendar-btn").on("click",function(){
					$("#account-calendar").hide(1000);
					$("#calendar").show(1000);
				});

				$(window).on("click",function(e){
					if(!$(e.target).closest('#open-calendar-btn').length &&  !$(e.target).closest('#calendar').length){
						$("#calendar").hide(1000);
						$("#account-calendar").show(1000);
					}
				});
			//====================== end calendar ======================


	// ============== End Publishing ==============================






	// ==================    Insaight    =====================================

	$(".dropdown  .dropdown-list  .dropdown-item").on("click",function () {
		// alert($(this).innerhtml())

		$(".dropdown .dropdown-active span").html($(this).html());
		$(".dropdown  .dropdown-list").hide();
	});

	$(".dropdown").hover(
		function () {
			$(".dropdown  .dropdown-list").show();
		},
		function () {
			$(".dropdown  .dropdown-list").hide();
		}
	);

	// ==================   End  Insaight    =====================================


// ======================   add  post      ================================

	$("#newPost .add-post-tabs li").on("click",function () {
		$("#newPost .add-post-tabs li").removeClass("active-tab");
		$(this).addClass("active-tab");

		$("#newPost .add-post-contents .content-item").removeClass("active-content");
		$($("#newPost .add-post-contents .content-item")[$(this).index()]).addClass("active-content");
	});



// ======================  end  add  post      =============================





// ==============================   New  post     =========================

	$("#scheduled-post").on("click",function(){
		$("#left-content-1").hide();
		$("#left-content-2").addClass("active-post-content");

	});

	$("#complete-post").on("click",function(){
		$("#left-content-1").show();
		$("#left-content-2").removeClass("active-post-content");
	})


//=========================   end   New post  ================


	// $(window).on("click",function (e) {
	// 	alert(e.target);
	// });
});

