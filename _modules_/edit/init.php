<?php



    define('VS_THEME',  Vars::getArrayVar($_COOKIE,'vs_theme','one-dark-pro') ); 


/**
 * 
 * Dónde conseguir las keys:
 * Gemini: aistudio.google.com/apikey (tiene tier gratuito generoso)
 * Grok: console.x.ai (tiene créditos gratuitos mensuales para usuarios de X Premium)
 * 
 * 
 */


/**
 * 
 * 
 * Ejemplo propuesto por Grok:
 * 
 *   curl https://api.x.ai/v1/chat/completions \
 *       -H "Content-Type: application/json" \
 *       -H "Authorization: Bearer $XAI_API_KEY" \
 *       -d '{
 *       "messages": [
 *        {
 *          "role": "system",
 *          "content": "You are a test assistant."
 *        },
 *        {
 *          "role": "user",
 *          "content": "Testing. Just say hi and hello world and nothing else."
 *        }
 *      ],
 *      "model": "grok-4-latest",
 *      "stream": false,
 *      "temperature": 0
 *   }
 * 
 * */