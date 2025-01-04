# bot.py

import json
import discord
import os
import requests

from discord.ext import commands
from dotenv import load_dotenv

load_dotenv()
TOKEN = os.getenv('DISCORD_TOKEN')
GUILD = os.getenv('DISCORD_GUILD')
API_BASE_URL = os.getenv('API_BASE_URL')

intents = discord.Intents.default()
intents.messages = True
intents.message_content = True
intents.members = True

bot = commands.Bot(command_prefix="!", intents=intents)

# Remove the default help command
bot.remove_command('help')

@bot.event
async def on_ready():
    print(f"Logged in as {bot.user.name}")

# Example command
@bot.command(name='ping')
async def ping(ctx):
    """
    Responds with pong.
    """
    print("ping bot command")
    await ctx.send("pong!")

# Get available commands
@bot.command(name='help')
async def help_command(ctx):
    """
    List available bot commands.
    """
    help_message = (
        "**Available Commands:**\n"
        "1. `!ping` - pong?\n"
        "2. `!events list` - Lists all events.\n"
        "3. `!events describe [name]` - Describes details of an event existing in Google Drive, if it exists.\n"
        "4. `!events reload` - Reloads all events on the site."
    )
    await ctx.send(help_message)


async def fetch_from_webserver(endpoint: str, param: str = None):
    """
    Fetch data from the webserver.
    Returns:
        tuple: (status_code, response_content)
    """
    try:
        url = f"{API_BASE_URL}/{endpoint}"
        if param:
            url += f"/{param}"
        print("url", url)
        response = requests.get(url)
        print("response", response)
        data = response.json()
        print("data", data)
        formatted_data = json.dumps(data, indent=4)
        return response.status_code, response.json() if response.headers.get('Content-Type') == 'application/json' else response.text
    except Exception as e:
        return None, str(e)

async def post_to_web_server(endpoint):
    """
    Sends POST the webserver and displays the response.
    Returns:
        tuple: (status_code, response_content)
    """
    try:
        url = f"{API_BASE_URL}/{endpoint}"
        if param:
            url += f"/{param}"
        response = requests.gpost(url)
        return response.status_code, None
    except Exception as e:
        return None, str(e)

@bot.command(name='events')
async def event(ctx, action: str, *, argument: str = None):
    """
    Handles the 'events' command with actions: list, describe [name], reload.
    Note: * handles '!event describe My Event Name', so
        argument = "My Event Name" instead of argument="My"
    """
    print("event bot command")
    format_json = lambda content: json.dumps(content, indent=4) if isinstance(content, dict) else content
    if action == "list":
        endpoint = "api/events"
        status_code, content = await fetch_from_webserver(endpoint)
        if status_code is None:
            await ctx.send(f"Bot failed to fetch data: {content}")
        elif status_code == 200:
            await ctx.send(f"Data from {endpoint}:\n```json\n{format_json(content)}\n```")
        else:
            await ctx.send(f"Webserver returned an error: {status_code}\n```json\n{format_json(content)}\n```")
    elif action == "describe":
        if argument:
            endpoint = "api/events"
            status_code, content = await fetch_from_webserver(endpoint, argument)
            if status_code is None:
                await ctx.send(f"Bot failed to fetch data: {content}")
            elif status_code == 200:
                await ctx.send(f"Data from {endpoint}:\n```json\n{format_json(content)}\n```")
            else:
                await ctx.send(f"Webserver returned an error: {status_code}\n```json\n{format_json(content)}\n```")
        else:
            await ctx.send("You need to specify an event name to describe!")

    elif action == "reload":
        status_code, error = await post_to_web_server("api/events/reload")
        if status_code != 200 or error != None:
            await ctx.send("Error in reloading events.")
        else:
            await ctx.send("Events are successfully reloaded on the site.")
    else:
        await ctx.send("Invalid action! Use 'list', 'publish [name]', or 'reload'.")

bot.run(TOKEN)
