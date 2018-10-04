<div class="modal-body">

	<style>
		.fb-reaction.fb-like .fb-icon{
			background-position: -119px -60px;
		}

		.fb-reaction.fb-love .fb-icon {
			background-position: -136px -60px;
		}

		.fb-reaction.fb-haha .fb-icon {
			background-position: -102px -60px;
		}

		.fb-reaction.fb-wow .fb-icon {
			background-position: -34px -77px;
		}

		.fb-reaction.fb-sad .fb-icon {
			background-position: 0 -77px;
		}

		.fb-reaction.fb-angry .fb-icon {
			background-position: -34px -60px;
		}

		.fb-reaction .fb-icon{
			background-image: url({{ url('img/reactions.png') }});
			width: 16px;
			display: inline-block;
			height: 16px;
			background-repeat: no-repeat;
			background-size: auto;
			background-color: rgb(255, 255, 255);
			border-radius: 16px;
		}

		span.fb-reaction-total {
			vertical-align: text-bottom;
		}

		.fb-shares .icon, .fb-comments .icon {
			background: url({{ url('img/X4u1G6Sqd9u.png') }});
			background-repeat: no-repeat;
			background-size: auto;
			width: 16px;
			height: 16px;
			display: inline-block;
		}

		.fb-comments .icon{
			background-position: -34px -143px;
		}

		.fb-shares .icon{
			background-position: -30px -195px;
		}

		.fb-shares,.fb-comments,.fb-reaction {
			display: inline-block;
			background: white;
			padding: 5px 10px;
			margin-bottom: 5px;
		}

		#proModal{{ $_mn }} .counts
		{
			padding: 6px;
			vertical-align: top;
		}
	</style>

	<div class="fb-reaction fb-like">
		<span class="counts">{{ $insights['like'] }}</span><span class="fb-icon"></span>
	</div>

	<div class="fb-reaction fb-love">
		<span class="counts">{{ $insights['love'] }}</span><span class="fb-icon"></span>
	</div>

	<div class="fb-reaction fb-wow">
		<span class="counts">{{ $insights['wow'] }}</span><span class="fb-icon"></span>
	</div>

	<div class="fb-reaction fb-haha">
		<span class="counts">{{ $insights['haha'] }}</span><span class="fb-icon"></span>
	</div>

	<div class="fb-reaction fb-sad">
		<span class="counts">{{ $insights['sad'] }}</span><span class="fb-icon"></span>
	</div>

	<div class="fb-reaction fb-angry">
		<span class="counts">{{ $insights['angry'] }}</span><span class="fb-icon"></span>
	</div>

	<div class="fb-comments">
		<span class="counts">{{ $insights['comments'] }}</span><span class="icon"></span>
	</div>

	<div class="fb-shares">
		<span class="counts">{{ $insights['shares'] }}</span><span class="icon"></span>
	</div>

</div>