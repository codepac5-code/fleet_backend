const express = require('express');
const appRealTime = express();
const http = require('http');
const serverRealTime = http.createServer(appRealTime);
const ioRealTime = require('socket.io')(serverRealTime, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"],
    },
});

const appNotifications = express();
const serverNotifications = http.createServer(appNotifications);

const PORT_REAL_TIME = 3000;

require('dotenv').config();

const Redis = require('ioredis');
const { emit } = require('process');

const redis = new Redis({
    host: process.env.REDIS_HOST,
    port: process.env.REDIS_PORT,
    password: process.env.REDIS_PASSWORD || null,
   // db: parseInt(process.env.REDIS_DB, 10) || 0,
});


const subRedis = new Redis({
  host: process.env.REDIS_HOST,
  port: process.env.REDIS_PORT,
  password: process.env.REDIS_PASSWORD || null,
 // db: parseInt(process.env.REDIS_DB, 10) || 0,
});




// ---------------------------------------------------

// redis.publish('private-bassam666', JSON.stringify(eventData))
//   .then((result) => {
//       console.log(`Event published with result: ${result}`);
//   })
//   .catch((err) => {
//       console.error('Error publishing event:', err);
//   });


// JSON.stringify(eventData)
// redis.publish('*', JSON.stringify(eventData));
//   // .then((result) => {
//   //     console.log(`Event published with result: ${result}`);
//   // })
//   // .catch((err) => {
//   //     console.error('Error publishing event:', err);
//   // });

const broadcastMessage = async (channel ,data) => {
        await subRedis.publish(channel, JSON.stringify(data));
        console.log(`event broadcasted: ${data}`);
    //}
};


redis.psubscribe('*', (err, count) => {
    if (err) {
        console.error("Failed to subscribe:", err);
    } else {
        console.log(`Subscribed to ${count} redis channels.`);
    }
});


redis.on('pmessage', ( pattern , channel , message ) => {
    message = JSON.parse(message);

    if(message.socket !== true ){
      if (channel.startsWith('public-notification') || channel.startsWith('private-notification')) {
        console.log('to notification channel');
        console.log(`Channel: ${channel}, Event: ${message.event} ,  data: ${message.data}`);
        ioNotification.to(channel).emit(channel + ':' + message.event , message.data);
    }
    else{
      // data = JSON.parse(message.data);
      console.log(`Channel: ${channel}, Event: ${message.event} ,  data: ${message.data}`);
      ioRealTime.to(channel).emit(channel + ':' + message.event , message.data );
    }

    }
});

// redis.publish(['*'], {"hi":"ss"});


//    socket.on('send-event', (data) => {

//     const eventData = JSON.stringify({
//         event: 'custom.event',
//         data: data
//     });

//     redis.publish('events-channel', eventData);
//     console.log('Event sent to Redis:', eventData);
// });


//--------------------------------------



ioRealTime.on('connection', (client) => {
    console.log('Client connected to Real-Time server' , client.id);


  client.on('join-private-channel', async ({ channelName, token }, callback) => {
    try {
        const response = await axios.post(process.env.APP_URL+'/broadcasting/auth', {
            channel_name: channelName,
            client_id: client.id,
        }, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });

        if (response.status === true) {
            client.join(channelName);

            redis.set(client.id+':channel:'+'$Ys#2?:'+channelName, true);
            console.log(`Joined channel: ${channelName}`);
            callback({ success: true });
        } else {
            callback({ error: 'Unauthorized' });
        }
    } catch (error) {
        console.error('Error during authentication:', error.response?.data || error.message);
        callback({ error: 'Server error' });
    }
});

//===================================

  client.on('unsubscribe', function(room) {
      console.log('leaving room', room);
      client.leave(room);
  });



  client.on('subscribe', function(room) {
      console.log('joining room', room);
      client.join(room);
  });


  // -------------- send event to channel
    client.on('emit-to-channel', function( data , response) {

    ioRealTime.to(data.channel).emit(data.channel + ":"+data.event , data.data);
    console.log('Channel:' + data.channel + ' Event:'+ data.event + " data: " + data.data);

    broadcastMessage(data.channel , {
      "event"   : data.event,
      "data"    : data.data,
      "socket"  : true
    });
    
  });

  client.on('disconnect', () => {
        console.log('Client disconnected:', client.id);
    });

    });
  



serverRealTime.listen(PORT_REAL_TIME, () => {
  console.log('\n===========================================');
  console.log(`| Real-Time server is running on port ${PORT_REAL_TIME} |`);
  console.log('===========================================');
});





///-----------------------------  Notification Server


// const appNotifications = express();
// const serverNotifications = http.createServer(appNotifications);

const PORT_NOTIFICATIONS = 4000;

const ioNotification = require('socket.io')(serverNotifications, {
  cors: {
      origin: "*",
      methods: ["GET", "POST"],
  },
});


// const app = express();
// const server = http.createServer(app);
// const io = new Server(server);



// const redis = new Redis({
//     host: process.env.REDIS_HOST,
//     port: process.env.REDIS_PORT,
//     password: process.env.REDIS_PASSWORD || null,
//    // db: parseInt(process.env.REDIS_DB, 10) || 0,
// });


// redisSubscriber.subscribe('notifications_*', (err, count) => {
//     if (err) {
//         console.error('Error subscribing to channel:', err);
//     } else {
//         console.log(`Subscribed to ${count} channel(s). Waiting for messages...`);
//     }
// });



// redisSubscriber.on('message', (channel, message) => {
//     console.log(`Received message from ${channel}: ${message}`);
//     io.emit('notification', message);
// });

ioNotification.on('connection', (client) => {
    console.log('A user connected to notification server');


    // ioNotification.to('public-notification-user').emit('public-notification-user' + ':' + 'new_notification', 
    // {'title':'مرحباااا','body':'ddddddddddd'});
    // console.log('send noooooooootiii');

    

     // -------------- send event to channel

    client.on('unsubscribe', function(room) {
      console.log('leaving room', room);
      client.leave(room);
  });


  client.on('subscribe', function(room) {
      console.log('joining notification room', room);
      client.join(room);
  });

    client.on('disconnect', () => {
        console.log('User disconnected');
    });
});

serverNotifications.listen(PORT_NOTIFICATIONS, () => {
  console.log('||||||||||||||||||||||||||||||||||||||||||||');
  console.log('===========================================');
  console.log(`| Notification server running on port ${PORT_NOTIFICATIONS} |`);
  console.log('===========================================\n');
});

