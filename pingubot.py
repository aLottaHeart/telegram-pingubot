

import re  
import datetime 
from random import randrange

import logging    

import telegram
from telegram import Update, ForceReply, ParseMode
from telegram import update
from telegram.bot import log
from telegram.ext import Updater, CommandHandler, MessageHandler, Filters, CallbackContext

import giphy_client
from giphy_client.rest import ApiException

import os
from dotenv import load_dotenv


logging.basicConfig(
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s', level=logging.INFO
)

logger = logging.getLogger(__name__)
load_dotenv()
token = token=os.getenv('BOT_TOKEN')
print(token)

def help_command(update: Update, context: CallbackContext) -> None:
    update.message.reply_text('Schreib einfach "/pingu" und wir schauen was passiert :D')

def pingu_command(update: Update, context: CallbackContext) -> None:
    print("pingu")
    chat_id = update.message.chat_id

    bot = telegram.Bot(token)
    
    try:
        with open('Spin_To_Win.txt', encoding="utf8") as f:
            lines = f.readlines()

        p = randrange(len(lines)-10)
        print(p)
        count = 5

        initialMessage = 1
        while initialMessage:
            try:
                datetime.datetime.strptime(lines[p][0:8],"%d.%m.%y")
                m = re.search('(\d+?.\d+?.\d+?), (\d+?:\d+?) - (.*?):(.*)', (lines[p]))
                print("Am " + m[1] + " um " + m[2] + " trug sich folgendes zu:")
                bot.send_message(chat_id, "*Am " + m[1] + " um " + m[2] + " trug sich folgendes zu:*", parse_mode=ParseMode.MARKDOWN)
                initialMessage = 0
            except ValueError as err:
                p += 1

        while count > 0:
            m = re.search('(\d+?.\d+?.\d+?), (\d+?:\d+?) - (.*?):(.*)', (lines[p]))
            message = "_" + m[3] + "_" + ":\n" + m[4]
            p += 1
            initialMessage = 1
            while initialMessage:
                try:
                    datetime.datetime.strptime(lines[p][0:8],"%d.%m.%y")
                    initialMessage = 0
                except ValueError as err:
                    message += ("\n" + lines[p])
                    p += 1
            bot.send_message(chat_id, message, parse_mode=ParseMode.MARKDOWN)
            p += 1
            count -= 1
                
        f.close()
    except TypeError as err:
        bot.send_message(chat_id, "ERROR 😭 (Tammo, hier ist 'n bugfix nötig)", parse_mode=ParseMode.MARKDOWN)


def gif_command(update: update, context: CallbackContext) -> None:
    print("gif")
    chat_id = update.message.chat_id
    bot = telegram.Bot(token)


           # create an instance of the API class
    api_instance = giphy_client.DefaultApi()
    api_key = 'dc6zaTOxFJmzC' # str | Giphy API Key.
    q = update.message.text[5:(len(update.message.text))]
    q = q.replace("ä", "ae")
    q = q.replace("ö", "oe")
    q = q.replace("ü", "ue")
    q = q.replace("ß", "ss")
    print(q)
    limit = 100 # int | The maximum number of records to return. (optional) (default to 25)
    offset = 0 # int | An optional results offset. Defaults to 0. (optional) (default to 0)
    rating = 'g' # str | Filters results by specified rating. (optional)
    lang = 'en' # str | Specify default country for regional content; use a 2-letter ISO 639-1 country code. See list of supported languages <a href = \"../language-support\">here</a>. (optional)
    fmt = 'json' # str | Used to indicate the expected response format. Default is Json. (optional) (default to json)

    try: 
        # Search Endpoint
        api_response = api_instance.gifs_search_get(api_key, q, limit=limit, offset=offset, rating=rating, lang=lang, fmt=fmt)
        random = randrange(0, len(api_response.data)-1)
        print(random)
        url = api_response.data[random].embed_url
        url = "https://i.giphy.com/" + url.split('/')[-1] + ".gif"
        print(url)
        bot.send_animation(chat_id, url)
        
    except ApiException as e:
        print("Exception when calling DefaultApi->gifs_search_get: %s\n" % e)
    except ValueError as e:
        bot.send_message(chat_id, "Für diese Suche habe ich keine gifs gefunden")


def main() -> None:
    """Start the bot."""
    updater = Updater(token)

    dispatcher = updater.dispatcher

    dispatcher.add_handler(CommandHandler("help", help_command))
    dispatcher.add_handler(CommandHandler("pingu", pingu_command))
    dispatcher.add_handler(CommandHandler("gif", gif_command))

    # dispatcher.add_handler(MessageHandler(Filters.text & ~Filters.command, echo))

    updater.start_polling()

    updater.idle()


if __name__ == '__main__':
    main()
