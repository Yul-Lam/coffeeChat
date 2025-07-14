import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.MIX_PUSHER_APP_KEY,
  cluster: process.env.MIX_PUSHER_APP_CLUSTER,
  forceTLS: true,             // or encrypted: true
});
