
const Redis = require('ioredis');
require('dotenv').config(); 

const redis = new Redis({
    host: process.env.REDIS_HOST,        
    port: process.env.REDIS_PORT,        
    password: process.env.REDIS_PASSWORD || null, 
    db: parseInt(process.env.REDIS_DB, 10) || 0,  
});

function sleep (time) {
    return new Promise((resolve) => setTimeout(resolve, time));
} 


function storeDriverInArea($driverId, $longitude, $latitude)
{
    geoHash = redis.geohash('drivers', $driverId);
    $areaKey = "drivers:area:$geoHash";
    redis.geoadd($areaKey, $longitude, $latitude, $driverId);
}