function enable_button(button) {

	//Get button
	var bt = button;

	//Get button switch state
	var state = bt.getAttribute( 'data-switch' );

	//Get target elements
	var target = document.getElementsByClassName( 'enable-target' );

	//On Click enable button changes:
	//data-switch ( ON / OFF ), innerHTML text between ( EDIT / CANCEL )
	//On Click elements target:
	//Remove / Set: disabled attribute & readonly attribute
	var default_target_state;
	var initial_text;

	if ( state == 'ON' ) {
		bt.innerHTML = 'Edit';
		bt.setAttribute( 'data-button', 'go' );
		bt.setAttribute( 'data-switch', 'OFF' );
		for (var i = target.length - 1; i >= 0; i--) {
			default_target_state = target[i].getAttribute( 'data-default' );
			target[i].setAttribute( default_target_state, default_target_state );
		};
	} else if ( state = 'OFF' ) {
		bt.innerHTML = 'Cancel';
		bt.setAttribute( 'data-button', 'no-go' );
		bt.setAttribute( 'data-switch', 'ON' );
		for (var i = target.length - 1; i >= 0; i--) {
			default_target_state = target[i].getAttribute( 'data-default' );
			target[i].removeAttribute( default_target_state );
		};
	}

}

function create_url_from_text( user_el ) {
    var input_target = document.querySelectorAll('input[data-target]');
    if (input_target) {
        var user_input = user_el.value;
        var rgex = /\s+/g;
        var emptyRgex = /[-_@\.$%<>^#!&'"`,:;\*\[\]\?\/\|\(\)\{\}\\\\]*/g;

        user_input = user_input.replace(emptyRgex, '').replace(rgex, '-').toLowerCase();

        for (var i = 0; i < input_target.length; i++) {
            input_target[i].value = user_input;
        }
    }
}

function get_text_count(user_el, target) {
    var max_count = 155;
    var user_text = user_el.value;
    var t_target = document.getElementById(target);
    var chars_left = max_count - user_text.length;

    t_target.innerHTML = chars_left;

    if (chars_left <= 15) {
        t_target.className = 'going-red';
    } else {
        t_target.className = '';
    }
}

function open_comment_box(parent, this_comment, parent_comment, parent_post) {
    var attach_to = parent.getAttribute('data-target');
    if (!document.getElementById('reply-' + attach_to)) {

        var box = document.getElementById('comment-box-template').cloneNode(true);
        var parent_box = document.getElementById(attach_to);
        var box_form = box.getElementsByTagName('form');
        var comment_parent = (parent_comment == 0) ? this_comment : parent_comment;
        
        
        var cpar = document.createElement('input');
        cpar.setAttribute('type', 'hidden');
        var ppar = cpar.cloneNode();
        cpar.setAttribute('name', 'commentparent');
        ppar.setAttribute('name', 'postparent');
        cpar.setAttribute('value', comment_parent);
        ppar.setAttribute('value', parent_post);

        //Close button
        var close_bt = document.createElement('button');
        close_bt.innerHTML = 'CANCEL';
        close_bt.className = 'button red-button';
        close_bt.addEventListener('click', function () {
            parent_box.removeChild(box);
        })

        box_form[0].appendChild(cpar);
        box_form[0].appendChild(ppar);
        box.appendChild(close_bt);

        box.id = 'reply-' + attach_to;
        box.className = 'user-reply-box';
        box.style.display = 'block';

        parent_box.appendChild(box);
    }
}

//NOT GOOD YET
function upload_file(el, request_path, response_container_id) {

    var rs_cont = document.getElementById(response_container_id);
    var XHR = new XMLHttpRequest();
    XHR.onreadystatechange = function () {
        if (XHR.readyState == 1) {
            rs_cont.innerHTML = '<div class="loading-msg"><p>Please Wait</p><div>';
        } else if( XHR.readyState == 4 && XHR.status == 200 ) {
            rs_cont.innerHTML = XHR.responseText;
        } else if (status == 404) {
            rs_cont.innerHTML = '<div class="warning-msg"><p>Could not upload.</p><div>';
        }
    }
    XHR.open('POST', request_path, true);
    XHR.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    XHR.send($data);
}

function please_wait() {
    var scr = document.createElement('div');
    var mes = document.createElement('h3');

    scr.style.cssText = 'width:100%; height:100%; top:0; left:0; position:fixed; background:rgba(255,255,255,.9); z-index:999999; padding-top:200px;';
    scr.style.textAlign = 'center';

    mes.setAttribute('class', 'wait-msg');

    mes.innerHTML = 'Your Request is being processed, Please wait...';

    scr.appendChild(mes);

    document.body.appendChild(scr);

}

/*******************/
/*     HELPERS     */
/*******************/

function change_all_select( select_el, target_select ) {
    document.getElementById(target_select).selectedIndex = select_el.selectedIndex;
}

/**
 * Return object keys and values inside a unordered list.
 * @param  object  obj pass the javascript object to be dumped
 * @return string  The markup holding the object key:values
 */
function dump(obj) {
    var output = '<ul>';
    for (var key in obj) {
        if (typeof (obj[key]) === 'object') {
            output += '<li>' + key + ' : ' + dump(obj[key]) + '</li>';
        } else {
            output += '<li>' + key + ' : ' + obj[key] + '</li>';
        }
    }
    output += '</ul>';
    return output;
}