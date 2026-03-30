
	(function($) {
	$('#OA_task_1').on('tap', '.mui-btn-yellow', function(event) {
					var elem = this;
					var li = elem.parentNode.parentNode;
					li.parentNode.removeChild(li);
					setTimeout(function() {
					$.swipeoutClose(li);
					}, 0);
					});

	$('#OA_task_1').on('tap', '.mui-btn-grey', function(event) {
					var elem = this;
					var li = elem.parentNode.parentNode;
					var list=$('#OA_task_1');
					var fristLi=$('#OA_task_1 li')[0];
					li.parentNode.insertBefore(li,fristLi);
					setTimeout(function() {
					$.swipeoutClose(li);
					}, 0);
						
					
				});
			
		
				var btnArray = ['确认', '取消'];
				

				})
	(mui);








	/*$('.mui-btn-grey').click(function(){

						var $this=$(this);
						var li=$this.parent().parent();
						$('#OA_task_1').prepend(li);

							
					})*/
				
				
