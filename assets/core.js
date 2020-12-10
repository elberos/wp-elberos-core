"use strict";

/*!
 *  Elberos
 *
 *  (c) Copyright 2020 "Ildar Bikmamatov" <support@elberos.org>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      https://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

function api_send_form($form, namespace, route, callback)
{
	var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
	var processData = true;	
	var send_data = {};
	var arr = $form.find('input');
	for (var i=0; i<arr.length; i++)
	{
		var $item = $(arr[i]);
		var name = $item.attr('name');
		send_data[name] = $item.val();
	}
	
	$.ajax({
		url: "/wp-json/" + namespace + "/" + route + "/",
		data: send_data,
		dataType: 'json',
		method: 'post',
		
		cache: false,
		contentType: contentType,
		processData: processData,
		xhrFields: { withCredentials: true },
		
		beforeSend: (function(send_data){ return function(xhr)
		{
			// this picks up value set in functions.php to allow authentication
			// to be passed through with function so WP knows to allow deletion.
			xhr.setRequestHeader('X-WP-Nonce', send_data['_wpnonce']);
		}})(send_data),
		
		success: (function(callback){
			return function(data, textStatus, jqXHR)
			{
				if (data.success)
				{
					callback(data);
				}
				else
				{
					callback(data);
				}
			}
		})(callback),
		
		error: (function(callback){
			return function(data, textStatus, jqXHR){
				
				callback({
					message: "System error",
					code: -100,
				});
				
			}
		})(callback),
	});
}