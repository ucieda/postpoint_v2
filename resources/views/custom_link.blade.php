<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"
	  xmlns:og="http://ogp.me/ns#"
	  xmlns:fb="https://www.facebook.com/2008/fbml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta name="description" content="{{ $description }}">

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="image" content="{{ $picture }}">
	<link rel="image_src" href="{{ $picture }}">
	<meta name="author" content="{{ $caption }}">

	<meta property="og:title" content="{{ $title }}"/>
	<meta property="og:description" content="{{ $description }}"/>
	<meta property="og:image" content="{{ $picture }}"/>
	<meta property="og:image:url" content="{{ $picture }}" />
	<meta property="og:image:width" content="470" />
	<meta property="og:image:height" content="265" />
	<meta property="og:site_name" content="{{ $title }}"/>

	<meta property="og:type" content="article"/>

	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="{{ parse_url($url, PHP_URL_HOST) }}">
	<meta name="twitter:title" content="{{ $title }}">
	<meta name="twitter:description" content="{{ $description }}">
	<meta name="twitter:domain" content="{{ parse_url($url, PHP_URL_HOST) }}">

	<meta itemprop="description" content="{{ $description }}">
	<meta itemprop="name" content="{{ $title }}">
	<meta itemprop="image" content="{{ $picture }}">
	<link itemprop="embedURL" href="{{ $url }}">

	<title>{{ $title }}</title>

</head>
<style type="text/css">
	div#conetnt {
		background: white;
		width: 50%;
		margin: auto;
		border-radius: 5px;
		overflow: hidden;
		border: 1px solid #ddd;
	}

	h1 {
		background: #ececec;
		padding: 5px 15px;
		margin: 0px;
	}

	h1 a {
		color: #868686;
		text-decoration: none;
		font-family: serif;
		font-size: 26px;
	}

	img {
		width: 100%;
	}

	p {
		margin: 0;
		padding: 0;
	}

	p.description {
		padding: 20px 10px;
		border-top: 1px solid #ddd;
		border-bottom: 1px solid #ddd;
	}
	a.readmore {
		float: right;
		padding: 5px 10px 10px;
		color: #2354b1;
		text-decoration: none;
		font-weight: bold;
	}
	.thumbnail {
		max-height: 360px;
		overflow: hidden;
	}
</style>
<body>
<div id="conetnt">
	<h1 class="title"><a href="{{ $url }}">{{ $title }}</a></h1>
	<div class="thumbnail"><a href="{{ $url }}"><img src="{{ $picture }}" border="0"></a></div>
	<p class="description">{{ $description }}</p>
	<a href="{{ $url }}" class="readmore">Read more</a>
</div>
</body>
</html>