const express = require('express');
const app = express();
const server = require('http').createServer(app);
const io = require('socket.io')(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"],
    },
});

require('dotenv').config();

const Redis = require('ioredis');
const { emit } = require('process');

const redis = new Redis({
    host: process.env.REDIS_HOST,
    port: process.env.REDIS_PORT,
    password: process.env.REDIS_PASSWORD || null,
   // db: parseInt(process.env.REDIS_DB, 10) || 0,
});



const eventData = {
  event: 'evve',
  data: {
    status: true,
    message: "hi event",
  }
};


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




redis.get('hi', (err, result) => {
  if (err) {
      console.error('Error retrieving data from Redis:', err);
  }
  else {
      console.log('Data retrieved:', result);
  }
});


redis.set("bb:hh","hh");
redis.set("bb:ee","ee");
redis.set("bb:yy","yy");


redis.keys('bb:*').then(function (keys) {
    var pipeline = redis.pipeline();
      keys.forEach(function (key) {
        console.log(key);
        //pipeline.del(key);
      });
        return pipeline.exec();
      });
const broadcastMessage = async (channel ,data) => {

  await redis.publish(channel, data);
  console.log(`event broadcasted: ${data}`);
};


// broadcastMessage('my-channel',{
//   "event": "MyRedisEvent",
//   "data": {
//       "key": "value"
//   },
//   "socket": null
// });


// redis.publish(
//   'hazem',
//   JSON.stringify({
//       event: 'MyRedisEvent',
//       data: { message: 'Hello from Redis!' },
//       socket: null,
//   })
// );

//-----------------------

function sleep (time) {
  return new Promise((resolve) => setTimeout(resolve, time));
}


redis.psubscribe('*', (err, count) => {
    if (err) {
        console.error("Failed to subscribe:", err);
    } else {
        console.log(`Subscribed to ${count} channels.`);
    }
});

redis.on('pmessage', (pattern, channel, message) => {
    message = JSON.parse(message);
    console.log(`Channel: ${channel}, Event: ${message.event}`);
    io.to(channel).emit(channel + ':' + message.event, message.data);
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







//---------------------------------------



io.on('connection', (client) => {
    console.log('Client connected:', client.id);



//     client.on('join-private-channel', async ({ channelName, token }, callback) => {
//     try {
//         const response = await axios.post(process.env.APP_URL+'/broadcasting/auth', {
//             channel_name: channelName,
//             client_id: client.id,
//         }, {
//             headers: {
//                 Authorization: `Bearer ${token}`,
//             },
//         });

//         if (response.status === true) {
//             client.join(channelName);

//             redis.set(client.id+':channel:'+'$Ys#2?:'+channelName, true);
//             console.log(`Joined channel: ${channelName}`);
//             callback({ success: true });
//         } else {
//             callback({ error: 'Unauthorized' });
//         }
//     } catch (error) {
//         console.error('Error during authentication:', error.response?.data || error.message);
//         callback({ error: 'Server error' });
//     }
// });

///===================================

  client.on('unsubscribe', function(room) {
      console.log('leaving room', room);
      client.leave(room);
  });

  client.on('driver-position',function (data){
    console.log(client.id);
    //storeDriverInArea($driverId, $longitude, $latitude);
    console.log(data);
  });



  client.on('subscribe', function(room) {
        //redis.set(client.id+':channel:'+'$Ys#2?:'+room, 'true');
      console.log('joining room', room);
      client.join(room);
      // io.emit('driver_511:new_order1',{});

      // sleep(11000).then(() => {

      //        console.log('data sending...');

      //       //  client.emit('driver_511:new_order',
      //       //  {
      //       //    "startAddress":"Damascus , mazah",
      //       //    "startLatitude":33.506502,
      //       //    "startLongitude":36.251901,
      //       //    "endAddress": "baramka , sana ",
      //       //    "endLatitude":33.506971,
      //       //    "endLongitude":36.287322,
      //       //    "distance":37,
      //       //    "time":45,
      //       //    "paymentMethod":"Cash",
      //       //    "totalAmount":72000,
      //       //    "subService":"Economy",
      //       //    "number":888
      //       //  });

      //    // }, 30000);


      //    // setInterval(() => {
      //       // console.log('data sending...');
      //       //  io.to('user_51').emit('driver_founded',
      //       //  {
      //       //  //   "startAddress":"Damascus , mazah",
      //       //    "startLatitude":33.506502,
      //       //    "startLongitude":36.251901,
      //       //  //   "endAddress": "baramka , sana ",
      //       //    "endLatitude":33.506971,
      //       //    "endLongitude":36.287322,
      //       //    "distance":37,
      //       //    "time":45,
      //       //    "paymentMethod":"Cash",
      //       //    "totalAmount":52000,
      //       //    "subService":"Economy",
      //       //    "number":666
      //       //  });

      //    // }
      //    // , 30000 );
      //  });

  });
      // setInterval(() => {




  // -------------- send event to channel
    client.on('emit-to-channel', function( data , callback) {

      io.to(data.channel).emit(data.channel+":"+data.event, data.data);

    //   broadcastMessage(data.channel,{
    //     "event":data.event,
    //     "data": data.data,
    //     "socket": null
    //   });

      console.log('send event:'+ data.event+" to channel:"+data.channel);

      if(data.event === 'research-on-driver'){
        console.log('order sending to driver...');
        io.emit('driver.1:new_order',data.data);
      }

    //   callback({ success : 'true' });
    //   const event = {
    //     event: 'MyRedisEvent',
    //     data: {
    //         message: 'Hello from Redis!',
    //     },
    //     socket: null,
    // };
      // const eventData = JSON.stringify({
      //   event:data.event,
      //   data: data.data
      // });


    //  redis.publish(data.channel, {
    //   event:data.event,
    //   data: data.data
    //   });




    // redis.publish('news', 'Hello world!');

     // redis.publish('*', {"hi":"ss"},"ccc");
  // .then((result) => {
  //     console.log(`Event 1published with result: ${result}`);
  // })
  // .catch((err) => {
  //     console.error('Error publishing event:', err);
  // });

     // console.log('0000000000');

    //   console.log('order sending to driver...');



      //io.emit(data.channel+":"+data.event, data.data);

      // io.to(data.channel).emit(data.channel+":"+data.event, data.data);
    //     redis.exists( client.id+':channel:'+'$Ys#2?:'+ data.channel ).then((exists) => {
    //           if (exists) {
    //              io.to(data.channel).emit(data.event, data.data);

    //             } else {
                    // callback({ error: 'you are not in this channel' });
    //             }
    // });
  });

  // client.on('accepted-order', (data) => {
  //   console.log('order accepted..');

  // });\


  // accepted...
  //


  client.on('accepted-order', (data, ack) => {
     console.log('order accepted..');
     io.to('order.1').emit('order.1:driver-founded',data);

    //console.log(`Received message: ${data}`);
    ack({'success':true});

  });


    io.on('user_51:research-on-driver',function(data){
      console.log('1111111111111111111111111');
      console.log(data);
    });


  // sleep(20000).then(() => {
  //   console.log('data 2 sending...');

  //   io.to('user_51').emit('driver_founded',
  //   {
  //   //   "startAddress":"Damascus , mazah",
  //     "startLatitude":33.506502,
  //     "startLongitude":36.251901,
  //   //   "endAddress": "baramka , sana ",
  //     "endLatitude":33.506971,
  //     "endLongitude":36.287322,
  //     "distance":37,
  //     "time":45,
  //     "paymentMethod":"Cash",
  //     "totalAmount":52000,
  //     "subService":"Economy",
  //     "number":666
  //   });
  // });













  client.on('disconnect', () => {

    // redis.keys(client.id+':channel:*').then(function (keys) {
    //     var pipeline = redis.pipeline();
    //     keys.forEach(function (key) {
    //         console.log(key);
    //       pipeline.del(key);
    //     });
    //     return pipeline.exec();
    //   });
        console.log('Client disconnected:', client.id);
    });
});

  // redis.exists("myKey").then((exists) => {
  //   if (exists) {
//     console.log("The key exists!");
//     } else {
//     console.log("The key does not exist!");
//     }
//   });

//   socket.on('check-room-membership', ({ roomName, clientId }, callback) => {
//     const room = io.sockets.adapter.rooms.get(roomName);
//     if (room) {
//         const isMember = room.has(clientId);
//         callback({ isMember });
//     } else {
//         callback({ isMember: false });
//     }
// });




const PORT = 3000;
server.listen(PORT, () => {
    console.log(`Socket.IO server running on port ${PORT}`);
});

































// const express = require('express');
// const http = require('http');
// const socketIo = require('socket.io');
// const Redis = require('ioredis');

// const app = express();
// const server = http.createServer(app);
// const io = socketIo(server, {
//     cors: {
//         origin: '*',
//         methods: ['GET', 'POST'],
//     },
// });





// const redis = new Redis();

// redis.psubscribe('*', (err, count) => {
//     if (err) {
//         console.error('Redis subscription error:', err);
//     }
// });

// redis.on('pmessage', (pattern, channel, message) => {
//     const data = JSON.parse(message);
//     console.log(`Channel: ${channel}, Data:`, data);
//     io.emit(channel, data);
// });

// io.on('connection', (client) => {
//     console.log('Client connected');
//     // socket.on('disconnect', () => {
//     //     console.log('Client disconnected');
//     // });

//     client.on('evve', (data) => {
//       console.log('Received "evve"');
//       console.log('Data:', data);
//     });







//   io.on('search', function(data) {
//     console.log('search..');

//     // sleep(11000).then(() => {
//     io.emit('driver-founded', {
//       'firstName':"mousab",
//       'lastName':"Al-Syoufi",
//       "latitude":33.506502,
//       "longitude":36.251901,
//       'userName':"Mousab Al-Syoufi",
//       // 'photo',
//       // 'gender',
//       // 'officeId',
//       // 'address',
//       // 'country',
//       // 'city',
//       // 'isConected',
//       // 'state':'',
//       // 'isActive',
//       // 'addressDescription',
//       // 'status',
//       // 'rating',
//       // 'rideCount',
//       // 'kmCount'
//     });
//   // });




// });





//     // io.to('roomName').on('eventName', function(data) {
//     //   console.log(data); });




//   client.on('unsubscribe', function(room) {
//       console.log('leaving room', room);
//       client.leave(room);
//   })

//   console.log(client.rooms);

//   client.on('send', function( data ) {
//     console.log('sending message');
//     io.sockets.in(data.channel).emit('message', data);
//   });


//   client.on('remove-channel',function(channel){
//       io.in(channel).socketsLeave(channel);
//   });

//     // setInterval(() => {
//     //   socket.emit('m',"hhhhhhhhhhhhhh");
//     // }, 5000);

//     // setInterval(() => {
//       // io.emit('m',{
//       //   "msg":"1111111111",
//       // });

//       io.emit('m',{ "msg" : "/accept"  , "status" : "completed" , "number" :78 } );

//       // io.emit('findDriver',{ "page" : "/wait_order_screen"  , "status" : "pending" , "number" :78 });

//       setInterval(() => {

//         io.to('user_511').emit('new_order',
//         {
//           "startAddress":"Damascus , mazah",
//           "startLatitude":33.506502,
//           "startLongitude":36.251901,
//           "endAddress": "baramka , sana ",
//           "endLatitude":33.506971,
//           "endLongitude":36.287322,
//           "distance":37,
//           "time":45,
//           "paymentMethod":"Cash",
//           "totalAmount":52000,
//           "subService":"Economy",
//           "number":888
//         });
//       }, 3000);



//     client.on('send_to_channel', function( data ) {

//     });

//     setInterval(() => {
//       io.to('user_51').emit('findDriver', { "msg":"profile"  , "number":51});
//     }, 3000);


//     setInterval(() => {
//       io.to('order_52').emit('order_52.findDriver', { "msg":"profile"  , "number":52});
//     }, 3000);

//     client.on('disconnect', () => {
//       console.log('User disconnected');
//     });


// });

// const PORT = 3000;

// server.listen(PORT, () => {
//     console.log(`server is running on port ${PORT}`);
// });


// //php artisan tinker
// //event(new \App\Events\TestEvent('Hello, Socket.io!'));



















// // const express = require('express');
// // const http = require('http');
// // const socketIo = require('socket.io');
// // const axios = require('axios');

// // const app = express();
// // const server = http.createServer(app);

// // const io = socketIo(server, {
// //   cors: {
// //     origin: "*",
// //     methods: ["GET", "POST"]
// //   }
// // });

// // const Redis = require('ioredis');
// // const redis = new Redis();


// // io.use(async (socket, next) => {
// //   const token = socket.handshake.auth.token;

// //   if (!token) {
// //     return next(new Error('Authentication error'));
// //   }

// //   try {
// //     const response = await axios.post(
// //       'http://localhost:8000/broadcasting/auth',
// //       {
// //         channel_name: socket.handshake.query.channel_name
// //       },
// //       {
// //         headers: {
// //           Authorization: `Bearer ${token}`
// //         }
// //       }
// //     );

// //     if (response.status === 200) {
// //       next();
// //     } else {
// //       next(new Error('Authorization failed'));
// //     }
// //   } catch (err) {
// //     console.error('Auth error:', err.message);
// //     next(new Error('Authentication failed'));
// //   }
// // });

// // io.on('connection', (socket) => {
// //   console.log('New client connected:', socket.id);


// //   socket.emit('message', 'Hello from server!');


// //   socket.on('disconnect', () => {
// //     console.log('Client disconnected:', socket.id);
// //   });
// // });


// // server.listen(3000, () => {
// //   console.log('Socket.io server running on port 3000');
// // });
