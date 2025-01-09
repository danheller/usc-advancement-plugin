(function(){

	// settings screen
	setuparrays();
	setupreviewpage();
})()

function setuparrays() {
	var buttons = document.querySelectorAll(".item a");
	buttons.forEach( function( btn ) {
		var btnclass = btn.className;
	
		// only show "add" button for the last item
		if( btnclass.indexOf("add") != -1 ) {
			if( btn.parentNode.nextElementSibling ) {
				btn.classList.add("hide");
			} else {
				btn.classList.remove("hide");
			}
		} else {
			if( parseInt( btn.parentNode.parentNode.getAttribute("data-count") ) < 1 ) {
				btn.classList.add("inactive");
			} else {
				btn.classList.remove("inactive");
			}
		}
		
		if( ! btn.getAttribute("data-setup") ) {
			btn.addEventListener("click",function(e) {
				e.preventDefault();
				var btnclass = btn.className;
				var itemnum = btn.parentNode.getAttribute("data-item");
				if( btnclass.indexOf("add") != -1 ) {
					var newitemnum = parseInt( btn.parentNode.parentNode.getAttribute("data-count") ) + 1;
					var newitem = document.createElement("div");
					newitem.classList.add("item");
					newitem.setAttribute("data-item",newitemnum);
					var btnparent = btn.parentNode.innerHTML;
					newitem.innerHTML = btnparent.replaceAll(itemnum,newitemnum).replaceAll(' data-setup="true"','');
										
					btn.parentNode.parentNode.append( newitem );
					newitem.querySelector("input").value = "";
					btn.parentNode.parentNode.setAttribute("data-count",newitemnum);
				} else {
					
					var newitemcount = parseInt( btn.parentNode.parentNode.getAttribute("data-count") ) - 1;
					var btnparent = btn.parentNode.parentNode;
					btnparent.setAttribute("data-count",newitemcount);
					btn.parentNode.remove();
					btnparent.querySelector("input").focus();
					btnparent.querySelector("input").blur();
				}
				setuparrays();
			});
			btn.setAttribute("data-setup",true);
		}
	});

	var inputs = document.querySelectorAll(".item input");
	inputs.forEach( function( inp ) {
		if( ! inp.getAttribute("data-setup") ) {
			inp.addEventListener("blur",function(e) {
				e.preventDefault();
				setuphiddeninputs();
			});
			inp.setAttribute("data-setup",true);
		}
	});
}

function setuphiddeninputs() {
	var arrayfields = document.querySelectorAll("tr[data-type='array']");
	arrayfields.forEach( function( af ) {
		var inps = af.querySelectorAll(".item input");
		inps.forEach( function( inp ) {
			var allvalues = new Array();
			inps.forEach(function(inv) {
				if( inv.value.trim() ) {
					allvalues.push( inv.value.trim().replaceAll('"','&quot;') );
				}
			});
			allvalues = JSON.stringify( allvalues );
			var hiddeninput = inp.parentNode.parentNode.previousElementSibling.querySelector("input[type='hidden']");
			hiddeninput.value = allvalues;
		});
	});
}

function setupreviewpage() {
	featurelinks = document.querySelectorAll('.feature-job');
	featurelinks.forEach( function( fl ) {
		fl.addEventListener('click', function(e){
			e.preventDefault();
			var formdata = new Object();
			formdata.action = 'feature_job';
			formdata.program = this.getAttribute("data-id");
			formdata.feature = 1;
			formdata._ajax_nonce = nonce;
			fl.classList.add("loading");
			if( this.classList.contains("on") ) {
				formdata.feature = 0;
			}
			jQuery.ajax({
				url: ajax,
				type: 'post',
				data: formdata,
				success: function( response ) {
//					console.log( 'status' );
//					console.log( response );
					fl.classList.remove("loading");
					if( '1' == response ) {
						fl.innerText = 'Don\'t Feature';
					} else {
						fl.innerText = 'Feature';
						var jobel = document.querySelector('.current.jobs-list li[data-job="'+fl.getAttribute("data-id")+'"]');
						if( jobel ) {
							jobel.classList.add("removing");
							setTimeout( function() {
								jobel.remove();
							}, 600 );
						}
					}
					fl.classList.toggle("on");
				},
				error: function( response ) {
				}
			});
		});
	});
	
	
	blocklinks = document.querySelectorAll('.block-job');
	blocklinks.forEach( function( blk ) {
		blk.addEventListener('click', function(e){
			e.preventDefault();
			var formdata = new Object();
			formdata.action = 'block_job';
			formdata.program = this.getAttribute("data-id");
			formdata._ajax_nonce = nonce;
			blk.classList.add("loading");

			jQuery.ajax({
				url: ajax,
				type: 'post',
				data: formdata,
				success: function( response ) {
//					console.log( 'status' );
//					console.log( response );
					blk.classList.remove("loading");

					if( '1' == response ) {
						blk.remove();
						window.location.reload();
					} else {
						alert('Sorry, there was a problem.');
					}
				},
				error: function( response ) {
				}
			});
		});
	});

	reqfetch = document.querySelector('.req-fetch');
	if( reqfetch ) {
		reqfetch.addEventListener("submit", function(e) {
			e.preventDefault();
			req = document.querySelector('input.req');
			var formdata = new Object();
			formdata.action = 'fetch_job';
			formdata.req = req.value.toLowerCase();
			alert(formdata.req);
			console.log( formdata );
			formdata._ajax_nonce = nonce;
			jQuery.ajax({
				url: ajax,
				type: 'post',
				data: formdata,
				success: function( response ) {
					console.log( response );
					if( '1' == response ) {
						alert( 'Fetching job ' + req.value + ' now' );
						req.value = 'req';
					} else {
						alert( 'Sorry, there was a problem. Please check the site error log.' );
					}
				},
				error: function( response ) {
				}
			});
		});
	}

	scrapebutton = document.querySelector('button.scrape');
	if( scrapebutton ) {
		scrapebutton.addEventListener("click", function(e) {
			e.preventDefault();
			scrapebutton.classList.add("loading");
			var formdata = new Object();
			formdata.action = 'start_scraper';
			formdata._ajax_nonce = nonce;
			jQuery.ajax({
				url: ajax,
				type: 'post',
				data: formdata,
				success: function( response ) {
	//				console.log( 'status' );
					scrapebutton.classList.remove("loading");
	
					if( '1' == response ) {
						alert( 'Scraping started' );
					} else {
						alert( 'Sorry, there was a problem. Please check the site error log.' );
					}
				},
				error: function( response ) {
				}
			});
		});
	}
}