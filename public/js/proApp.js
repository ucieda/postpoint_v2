
var proApp = {

	options: {
		'templates': {
			'loader': '<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba( 0, 0, 0, 0.2 ); z-index: 99999;"></div>',

			'loaderModal': function()
			{
				return '<div style="position: absolute; width: 100%; height: 100%; background: rgba( 0, 0, 0, 0.2 ); z-index: 999;"></div>';
			},

			'modal': '<div class="modal col-sm-12 in {fade}" data-keyboard="false"><div class="modal-dialog modal-md addAccount" style="{width}" role="document"><div class="modal-content"><div class="modal-header modalHeader"><button type="button" class="close" data-dismiss="modal" style="margin-top: -9px;">&times;</button><h4 class="modal-title">{header}</h4></div>{body}</div></div></div>',

			'alert':
				'<div class="pro-alert" style="position: fixed; width: 100%; height: 100%; top: 0; left: 0; background: rgba(0,0,0,0.3); z-index: 999999999 !important;">' +
					'<div style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; margin: auto; width: 500px; min-height: 200px;height: fit-content;border-radius: 15px;" class="alert alert-block alert-{type}">' +
                    '<div class="icon-info text-center"><img src="../public/img/info.png" /></div>'    +
						'<div style="text-align: center;font-size: 18px;font-weight: 500; padding: 10px; min-height: 70px;height: fit-content;margin-top:30px;overflow: auto;">{text}</div>' +
						'<div style="padding-top: 0px;text-align: center;"><button class="btn btn-default okButton" type="button">Ok, I understand</button></div>' +
					'</div>' +
				'</div>'
		}
	},

	modalsCount: 0,

	confirm: function ( title , text , fnOkButton , okButton , cancelButton , afterClose )
	{
		okButton = typeof okButton != 'undefined' ? okButton : 'Ok';
		cancelButton = typeof cancelButton != 'undefined' ? cancelButton : 'Cancel';
		afterClose = typeof afterClose != 'undefined' ? afterClose : true;

		var modalNumber = proApp.modal( title , '<div class="modal-body">' + text + '</div><div class="modal-footer"><button class="btn btn-success ConfirmOkButton" type="button">'+okButton+'</button> <button class="btn btn-cancel" data-dismiss="modal" type="button">'+cancelButton+'</button></div>' );

		$( modalNumber[2] + ' .ConfirmOkButton' ).click(function( )
		{
			fnOkButton( modalNumber[2] );

			if( afterClose )
			{
				$( modalNumber[2] ).modal( 'hide' );
			}
		});
	},

	modal: function ( title, body, options )
	{
		var t = this;

		title = typeof title == 'function' ? title() : title ;
		body = typeof body == 'function' ? body() : body ;

		options = typeof options !== 'object' ? {} : options;

		var modalType = 'type' in options ? modalType : 'btn-success',
			modalWidth = 'width' in options ? 'width: ' + (options['width'].toString().match(/(%|px)/)==null ? options['width'] + "%" : options['width']) + ' !important;' : '' ,
			modalFade = 'fade' in options && options['fade'] === false ? false : true;

		t.modalsCount++;

		var modalTpl = t.options.templates.modal
			.replace( '{fade}' , ( modalFade ? 'fade' : '' ) )
			.replace( '{width}' , modalWidth )
			.replace( '{header}' , title )
			.replace( '{body}' , body );

		var el = t.parseHTML( modalTpl ),
			newId = 'proModal' + t.modalsCount;

		el.firstChild.id = newId;

		document.body.insertBefore( el , document.body.lastChild);

		$("#" + newId).modal('show').on("hidden.bs.modal", function()
		{
			if( typeof options['autoReload'] != 'undefined' && options['autoReload'] )
			{
				proApp.reload( );
			}
			if( typeof options['onHide'] == 'function' )
			{
				options['onHide']( );
			}
			$( this ).remove( );
		});

		return [ newId , t.modalsCount , '#' + newId ];
	},

	modalLoading: function ( _mn , onOff )
	{
		_mn = _mn.toString().replace( /^\#/ , '' );
		if( typeof onOff == 'undefined' || onOff )
		{
			var cntnt = document.getElementById( _mn );
			if( cntnt === null || cntnt.getElementsByClassName('modal-content') === null || !(0 in cntnt.getElementsByClassName('modal-content')) ) return;
			cntnt = cntnt.getElementsByClassName('modal-content')[0];
			var tpl = this.parseHTML( this.options.templates.loaderModal() );
			tpl.firstChild.setAttribute('id' , _mn + '-loading-element261272');

			cntnt.insertBefore( tpl , cntnt.firstChild );
		}
		else if( document.getElementById( _mn + '-loading-element261272' ) !== null )
		{
			var prnt = document.getElementById( _mn + '-loading-element261272' );
			prnt.parentNode.removeChild(prnt);
		}
	},

	modalWidth: function( _mn , width )
	{
		_mn = _mn.toString().replace( /^\#/ , '' );

		$("#" + _mn + '>.modal-dialog' ).attr("style", "width: " + width + "% !important");
	},

	loadModal: function ( url, modalTitle, postParams, modalOptions )
	{
		var t = this,
			newModal = t.modal( modalTitle||'...', '' , modalOptions );

		postParams = typeof postParams != 'undefined' ? postParams : {};
		postParams['_mn'] = newModal[1];
		postParams['_token'] = $("meta[name=csrf-token]").attr('content');

		t.modalLoading( newModal[2] , 1 );

		$.ajax({
			url: url,
			method: 'POST',
			data: postParams,
			success: function ( result )
			{
				t.modalLoading( newModal[2] , 0 );

				result = t.jsonResjult( result );
				if( result['status'] == 'ok' && typeof result['html'] != 'undefined' )
				{
					if( typeof result['title'] != 'undefined' )
					{
						$( "#" + newModal[0] ).find(".modal-header > .modal-title").text( t.htmlspecialchars_decode( result['title'] ) );
					}

					$( "#" + newModal[0] ).find(".modal-header").after( t.htmlspecialchars_decode( result['html'] ) );

				}
				else if( result['status'] == 'error' )
				{

				}
			},
			error: function (jqXHR, exception)
			{
				var msg = '';
				if (jqXHR.status === 0) {
					msg = 'Not connect.';
				} else if (jqXHR.status == 404) {
					msg = 'Requested page not found. [404]';
				} else if (jqXHR.status == 500) {
					msg = 'Internal Server Error [500].';
				} else if (exception === 'parsererror') {
					msg = 'Requested JSON parse failed.';
				} else if (exception === 'timeout') {
					msg = 'Time out error.';
				} else if (exception === 'abort') {
					msg = 'Ajax request aborted.';
				} else {
					msg = 'Uncaught Error.';
				}
				alert( msg );
			}
		});
	},

	parseHTML: function ( html )
	{
		var range = document.createRange();
		var documentFragment = range.createContextualFragment( html );
		return documentFragment;
	},

	loading: function ( onOff )
	{
		if( typeof onOff == 'undefined' || onOff )
		{
			var tpl = this.parseHTML(this.options.templates.loader);
			tpl.firstChild.setAttribute('id' , 'pro-loading-element261272');
			document.body.insertBefore( tpl , document.body.lastChild);
		}
		else if( document.getElementById( 'pro-loading-element261272' ) !== null )
		{
			var prnt = document.getElementById( 'pro-loading-element261272' );
			prnt.parentNode.removeChild(prnt);
		}
	},

	jsonResjult: function ( json )
	{
		if( typeof json == 'object' )
		{
			return json;
		}

		var result;
		try
		{
			result = JSON.parse( json );
		}
		catch(e)
		{
			result = {
				'status': 'parse-error',
				'error': e
			};
		}
		return result;
	},

	htmlspecialchars_decode: function (string, quote_style)
	{
		var optTemp = 0,
			i = 0,
			noquotes = false;
		if(typeof quote_style==='undefined')
		{
			quote_style = 2;
		}
		string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
		var OPTS ={
			'ENT_NOQUOTES': 0,
			'ENT_HTML_QUOTE_SINGLE': 1,
			'ENT_HTML_QUOTE_DOUBLE': 2,
			'ENT_COMPAT': 2,
			'ENT_QUOTES': 3,
			'ENT_IGNORE': 4
		};
		if(quote_style===0)
		{
			noquotes = true;
		}
		if(typeof quote_style !== 'number')
		{
			quote_style = [].concat(quote_style);
			for (i = 0; i < quote_style.length; i++){
				if(OPTS[quote_style[i]]===0){
					noquotes = true;
				} else if(OPTS[quote_style[i]]){
					optTemp = optTemp | OPTS[quote_style[i]];
				}
			}
			quote_style = optTemp;
		}
		if(quote_style & OPTS.ENT_HTML_QUOTE_SINGLE)
		{
			string = string.replace(/&#0*39;/g, "'");
		}
		if(!noquotes){
			string = string.replace(/&quot;/g, '"');
		}
		string = string.replace(/&amp;/g, '&');
		return string;
	},

	htmlspecialchars: function ( string, quote_style, charset, double_encode )
	{
		var optTemp = 0,
			i = 0,
			noquotes = false;
		if(typeof quote_style==='undefined' || quote_style===null)
		{
			quote_style = 2;
		}
		string = typeof string != 'string' ? '' : string;

		string = string.toString();
		if(double_encode !== false){
			string = string.replace(/&/g, '&amp;');
		}
		string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');
		var OPTS = {
			'ENT_NOQUOTES': 0,
			'ENT_HTML_QUOTE_SINGLE': 1,
			'ENT_HTML_QUOTE_DOUBLE': 2,
			'ENT_COMPAT': 2,
			'ENT_QUOTES': 3,
			'ENT_IGNORE': 4
		};
		if(quote_style===0)
		{
			noquotes = true;
		}
		if(typeof quote_style !== 'number')
		{
			quote_style = [].concat(quote_style);
			for (i = 0; i < quote_style.length; i++)
			{
				if(OPTS[quote_style[i]]===0)
				{
					noquotes = true;
				}
				else if(OPTS[quote_style[i]])
				{
					optTemp = optTemp | OPTS[quote_style[i]];
				}
			}
			quote_style = optTemp;
		}
		if(quote_style & OPTS.ENT_HTML_QUOTE_SINGLE)
		{
			string = string.replace(/'/g, '&#039;');
		}
		if(!noquotes)
		{
			string = string.replace(/"/g, '&quot;');
		}
		return string;
	},

	alert: function( message , type , okBtn )
	{
		type = type || 'danger';
		okBtn = okBtn || false;

		var tpl = this.options.templates.alert.replace('{text}' , message).replace('{type}' , type);

		var htmlFrags = this.parseHTML( tpl );
		htmlFrags.firstChild.getElementsByClassName('okButton')[0].addEventListener('click' , function()
		{
			var t = this;
			while( (' '+t.parentNode.className+' ').indexOf(' pro-alert ')==-1 )
			{
				t = t.parentNode;
			}
			t.parentNode.remove();
		});
		document.body.insertBefore( htmlFrags , document.body.lastChild);
	},

    ajaxResultCheck: function (res)
    {
        if( typeof res != 'object' )
        {
            this.alert( 'Error!' );
            return false;
        }

        if( typeof res['status'] == 'undefined' )
        {
            this.alert( 'Error!' );
            return false;
        }

        if( res['status'] == 'error' )
        {
            this.alert( typeof res['error_msg'] == 'undefined' ? 'Error!' : res['error_msg'] );
            return false;
        }

        if( res['status'] == 'ok' )
            return true;

        // else

        this.alert( 'Error!' );
        return false;
    },

    ajax: function ( url , params , func )
    {
        var t = this;
        t.loading(true);

        $.post( url , params , function( result )
        {
            t.loading(false);
            if( proApp.ajaxResultCheck( result ) )
            {
                func( result );
            }
        });
    },

    zeroPad: function(n)
	{
		return n > 9 ? n : '0' + n;
	},

	spintax: function( text )
	{
		var matches, options, random;

		var regEx = new RegExp(/{([^{}]+?)}/);

		while((matches = regEx.exec(text)) !== null) {
			options = matches[1].split("|");
			random = Math.floor(Math.random() * options.length);
			text = text.replace(matches[0], options[random]);
		}

		return text;
	}
}

$(document).ready(function()
{
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
});