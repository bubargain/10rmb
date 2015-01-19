// JavaScript Document
	var swidth=$(document).width()/2-2;
	$("#listcontent .list_item").width(swidth);
	//alert(swidth);
	var handler = $('#listcontent .list_item');
      handler.wookmark({
          // Prepare layout options.
          autoResize: true, // This will auto-update the layout when the browser window is resized.
          container: $('#listcontent'), // Optional, used for some extra CSS styling
         // offset: 5, // Optional, the distance between grid items
          //outerOffset: 10, // Optional, the distance to the containers border
          itemWidth: swidth // Optional, the width of a grid item
      });


		var myScroll,
		pullDownEl, pullDownOffset,
		pullUpEl, pullUpOffset,
		generatedCount = 0;
		

	/**
	 * 下拉刷新 （自定义实现此方法）
	 * myScroll.refresh();		// 数据加载完成后，调用界面更新方法
	 */
	 	
	function pullDownAction () {
		setTimeout(function () {	// <-- Simulate network congestion, remove setTimeout from production!
			//var i;
			//for (i=0; i<6; i++) {
			//	var html='<div class="index_item"><a href="javascript:void(0)"><img src="images/indexc_02.jpg"><div><span>who believe in simplicitywho believe .</span>ENDS IN 2 DAYS</div></a></div>';
			//	$(".index_content").append(html);
			//}
			myScroll.refresh();		//数据加载完成后，调用界面更新方法   Remember to refresh when contents are loaded (ie: on ajax completion)
		}, 1000);	// <-- Simulate network congestion, remove setTimeout from production!
	}
	
	/**
	 * 滚动翻页 （自定义实现此方法）
	 * myScroll.refresh();		// 数据加载完成后，调用界面更新方法
	 */
	function pullUpAction () {
		setTimeout(function () {	// <-- Simulate network congestion, remove setTimeout from production!
			var i;
			var page= parseInt($('#page').val())+1;
			//for (i=0; i<6; i++) {
			//获取新的商品
				$url= "index.php?_c=api&_a=newevents&count=2&page="+page;
			
				$.getJSON($url, function(data) {			
					
					for(i=0;i<data.count;i++)
					{	
						var html='<div class="list_item"><a href="javascript:void(0)"><img src="'+data.events[i]["pic_link"]+'"><p><em>-25%</em></p><span>only 10 left</span></a><div><a href="javascript:void(0)" class="like"></a>$ 52<span>Rebate:<br><em>$ 1.5</em></span></div></div>';
						console.log(html);
						$("#listcontent").append(html);
					}
					
				});	
				
			//}
			$('#page').val(page); //页数+1
			if (handler.wookmarkInstance) {
            	handler.wookmarkInstance.clear();
          	}
		  handler = $('#listcontent .list_item');
          handler.wookmark({
          // Prepare layout options.
          autoResize: true, // This will auto-update the layout when the browser window is resized.
          container: $('#listcontent'), // Optional, used for some extra CSS styling
         // offset: 5, // Optional, the distance between grid items
          //outerOffset: 10, // Optional, the distance to the containers border
          itemWidth: swidth // Optional, the width of a grid item
      });
			myScroll.refresh();		// 数据加载完成后，调用界面更新方法 Remember to refresh when contents are loaded (ie: on ajax completion)
		}, 1000);	// <-- Simulate network congestion, remove setTimeout from production!
	}
	/**
	 * 初始化iScroll控件
	 */
	function loaded() {
		pullDownEl = document.getElementById('pullDown');
		pullDownOffset = pullDownEl.offsetHeight;
		pullUpEl = document.getElementById('pullUp');	
		pullUpOffset = pullUpEl.offsetHeight;
		
		myScroll = new iScroll('wrapper', {
			scrollbarClass: 'myScrollbar', /* 重要样式 */
			useTransition: false, /* 此属性不知用意，本人从true改为false */
			topOffset: pullDownOffset,
			onRefresh: function () {
				if (pullDownEl.className.match('loading')) {
					pullDownEl.className = '';
					pullDownEl.querySelector('.pullDownLabel').innerHTML = 'Refreshing...';
				} else if (pullUpEl.className.match('loading')) {
					pullUpEl.className = '';
					pullUpEl.querySelector('.pullUpLabel').innerHTML = 'Loading more...';
				}
			},
			onScrollMove: function () {
				if (this.y > 5 && !pullDownEl.className.match('flip')) {
					pullDownEl.className = 'flip';
					pullDownEl.querySelector('.pullDownLabel').innerHTML = 'Please Release';
					this.minScrollY = 0;
				} else if (this.y < 5 && pullDownEl.className.match('flip')) {
					pullDownEl.className = '';
					pullDownEl.querySelector('.pullDownLabel').innerHTML = 'Pull to refresh...';
					this.minScrollY = -pullDownOffset;
				} else if (this.y < (this.maxScrollY - 5) && !pullUpEl.className.match('flip')) {
					pullUpEl.className = 'flip';
					pullUpEl.querySelector('.pullUpLabel').innerHTML = 'Release...';
					this.maxScrollY = this.maxScrollY;
				} else if (this.y > (this.maxScrollY + 5) && pullUpEl.className.match('flip')) {
					pullUpEl.className = '';
					pullUpEl.querySelector('.pullUpLabel').innerHTML = 'View More...';
					this.maxScrollY = pullUpOffset;
				}
			},
			onScrollEnd: function () {
				if (pullDownEl.className.match('flip')) {
					pullDownEl.className = 'loading';
					pullDownEl.querySelector('.pullDownLabel').innerHTML = 'Loading...';				
					pullDownAction();	// Execute custom function (ajax call?)
				} else if (pullUpEl.className.match('flip')) {
					pullUpEl.className = 'loading';
					pullUpEl.querySelector('.pullUpLabel').innerHTML = 'Loading...';				
					pullUpAction();	// Execute custom function (ajax call?)
				}
			}
		});
		
		setTimeout(function () { document.getElementById('wrapper').style.left = '0'; }, 800);
	}
		//初始化绑定iScroll控件 
	document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
	document.addEventListener('DOMContentLoaded', loaded, false); 
