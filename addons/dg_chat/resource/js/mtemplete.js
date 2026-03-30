{{each list as question qindex}}
<dd class="wl_msg_item">
	<div class="wl_top_wkBox">
	<span class="wl_top_boxFl">
					<i class="uesr_head">
						<img src="{{question.pay_avatar}}">
					</i>
					<b>{{question.pay_nickname}}</b>
				</span>
				<span class="wl_price">¥<var>{{question.pay_money}}</var></span>
	</div>
			<div class="whisper_question">
				{{question.ask_content}}
			</div>

			{{if question.answer}}
			 <div class="whisper_answer">
				{{each question.answer as answer aindex}}
				<ul class="whisper_answer_wkBox">
					<li class="uesr_head">
						<img src="{{head_imgurl}}">
					</li>
					<li class="wl_answer_boxFl">
						<div class="audio_box">
							<i attr-src="{{answer.answer_content}}" class="ico_audio"></i><p>点击播放</p><b class="second">{{answer.time_last}}"</b><a class="audio_box_play" href="javascript:;"></a>
						</div>
					</li>
				</ul>
				{{/each}}
				</div>	
			{{else}}
			 <div class="answer_box">
							<a class="wt_btn_answer" href="">回答</a>
							<b class="whisper_tips_1">48小时没有回答，付款将自动退回</b>
			 </div>
		    {{/if}}
</dd>
{{/each}}