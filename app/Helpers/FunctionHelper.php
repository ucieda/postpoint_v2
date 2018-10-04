<?php

function viewModal( string $viewName , $title = null , $parameters = null )
{
	$modalNum = \Illuminate\Support\Facades\Input::post('_mn');

	$parameters = is_array($parameters) ? $parameters : (is_array($title) ? $title : []);
	$parameters['_mn'] = is_numeric($modalNum) && $modalNum > 0 ? (int)$modalNum : 0;

	$returnArr = [
		'status'	=>	'ok',
		'html'		=>	htmlspecialchars( view( $viewName , $parameters ) )
	];

	if( is_array($parameters) && is_string($title) )
	{
		$returnArr['title'] = (string)$title;
	}

	return $returnArr;
}

function setHomeSettings()
{
	if( !session()->has('home_settings_show_groups') )
	{
		session(['home_settings_show_groups' => true]);
	}

	if( !session()->has('home_settings_show_pages') )
	{
		session(['home_settings_show_pages' => true]);
	}

	if( !session()->has('home_settings_show_hiddens') )
	{
		session(['home_settings_show_hiddens' => true]);
	}
}

function printForJs(string $string)
{
	return json_encode( htmlspecialchars($string , ENT_QUOTES) );
}

function getUrlInfo(string $url)
{
	$doc = new DOMDocument();

	@$doc->loadHTML( mb_convert_encoding( \App\Lib\Curl::getURL($url) , 'HTML-ENTITIES', 'UTF-8') );

	$titleNode		= $doc->getElementsByTagName('title')->item(0);

	$title			= $titleNode ? $titleNode->nodeValue : '...';
	$description	= '...';
	$image			= '';

	$metas			= $doc->getElementsByTagName('meta');

	for ($i = 0; $i < $metas->length; $i++)
	{
		$meta = $metas->item($i);

		if($meta->getAttribute('name') == 'description')
		{
			$description = textShorter( $meta->getAttribute('content') );
		}
		else if($meta->getAttribute('property') == 'og:image'){
			$image	= $meta->getAttribute('content');
		}
	}

	return [
		'title'			=>	$title,
		'description'	=>	$description,
		'image'			=>	$image,
		'domain'		=>	parse_url($url, PHP_URL_HOST)
	];
}

function textShorter( string $text , int $length = 500 )
{
	return mb_substr( $text , 0 , $length , 'UTF-8' ) . ( mb_strlen($text , 'UTF-8') > $length ? '...' : '' );
}

function spintax( string $text )
{
	return preg_replace_callback(
		'/\{(((?>[^\{\}]+)|(?R))*)\}/x',
		function ($text)
		{
			$text = spintax( $text[1] );
			$parts = explode('|', $text);

			return $parts[ array_rand($parts) ];
		},
		$text
	);
}

function dirSize($directory)
{
	if( !is_dir( $directory ) )
	{
		return 0;
	}

	$size = 0;

	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file)
	{
		$size+=$file->getSize();
	}

	return round( $size / 10000 ) / 100;
}

function uploadMaxSizeChecker($cmd, $result, $args, $elfinder)
{
	$uploadedSizes = 0;
	$uploads = isset($result['FILES']['upload']['size']) ? $result['FILES']['upload']['size'] : 0;

	foreach($uploads AS $uploadSize)
	{
		$uploadedSizes += $uploadSize;
	}

	$uploadedSizes = round($uploadedSizes / 10000) / 100;

	$uploadDir = 'uploads/user_' . Auth::id();

	$dirSize = dirSize( public_path($uploadDir) ) + $uploadedSizes;

	$maxAllowedSiz = \Illuminate\Support\Facades\Auth::user()->User_role ? \Illuminate\Support\Facades\Auth::user()->User_role->max_upload_mb : 0;

	if( $maxAllowedSiz && $maxAllowedSiz > 0 && $maxAllowedSiz < $dirSize )
	{
		print json_encode(['error'	=>	'Sizə ayrılmış maksimum disk həcmini aşdınız! Əməliyyat dayandırıldı! (Max: '. $maxAllowedSiz .' Mb)']);
		exit();
	}
}

function recordsPerPage()
{
	$rpp = \Illuminate\Support\Facades\Auth::user()->records_per_page;

	return $rpp > 10 ? $rpp : 10;
}

function siteOption( $key , $defaultValue = null )
{
	$val = \App\Option::selectCached( )->$key ?? null;
	return empty($val) ? $defaultValue : $val;
}

function dateFormat( $forJs = false)
{
	$dateFormat = siteOption('date_format');

	$formats = [
		'YYYY-MM-DD'	=>	'Y-m-d' ,
		'MM/DD/YYYY'	=> 	'm/d/Y',
		'DD-MM-YYYY'	=> 	'd-m-Y'
	];

	if( $forJs === false )
	{
		return isset( $formats[$dateFormat] ) ? ( $forJs ? str_replace('yyyy' , 'yy' , strtolower($dateFormat)) : $formats[$dateFormat] ) : 'Y-m-d';
	}
	else if( $forJs === 1 )
	{
		return isset( $formats[$dateFormat] ) ? str_replace('yyyy' , 'yy' , strtolower($dateFormat)) : 'Y-m-d';
	}
	else
	{
		return isset( $formats[$dateFormat] ) ? $dateFormat : 'Y-m-d';
	}

}

function generateLink()
{
	/*if($fbAccountDefaultApp->row("appid") != "193278124048833"){
		if(trim($picture) != "" || trim($name) != "" || trim($caption) != "" || trim($description) != ""){
			$buildLink = "?link=".substr($this->spintax->get($link),0,200);
			$buildLink .= "&picture=".$this->spintax->get($picture);
			$buildLink .= "&name=".substr($this->spintax->get($name),0,200);
			$buildLink .= "&caption=".$this->spintax->get($caption);
			$buildLink .= "&description=".substr($this->spintax->get($description),0,200);
			$params['link'] = urlencode(base_url("/page/generate/".$buildLink));
		}
	}else{
		$params['picture'] = urlencode($this->spintax->get($picture));
		$params['name'] = urlencode($this->spintax->get($name));
		$params['caption'] = urlencode($this->spintax->get($caption));
		$params['description'] = urlencode($this->spintax->get($description));
	}*/
}

function sendConfirmationEmail($email , $userId , $token)
{
	$link	= url('confirm_user/' . $userId . '/' . $token);

	$title	= siteOption('site_name') . ' | Confirm User Registration';

	if( siteOption('mail_protocol') == 'mail' )
	{
		$headers = "From: info@".request()->getHost()."\r\n";
		$headers .= "Reply-To: info@".request()->getHost()."\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

		$text = view('mails.email_confirmation')->with('link', $link)->render();

		mail($email , $title , $text , $headers );
	}
	else
	{
		Config::set('mail.driver' , siteOption('mail_protocol'));
		Config::set('mail.host' , siteOption('smtp_host'));
		Config::set('mail.port' , siteOption('smtp_port'));
		Config::set('mail.username' , siteOption('smtp_user'));
		Config::set('mail.password' , siteOption('smtp_pass'));
		Config::set('mail.encryption' , siteOption('smtp_encryption'));

		Mail::send('mails.email_confirmation', ['link'	=>	$link], function($message) use( $email , $title )
		{
			$message->to($email)->subject( $title );
			$message->from( siteOption('smtp_user') , siteOption('site_name'));
		});
	}
}

function crontab_installed()
{
	if( is_callable('shell_exec') && is_callable('exec') &&
		in_array('shell_exec' , explode("," , ini_get('disable_functions'))) === false &&
		in_array('exec' , explode("," , ini_get('disable_functions'))) === false )
	{
		return !empty(shell_exec("crontab -l"));
	}

	return false;
}