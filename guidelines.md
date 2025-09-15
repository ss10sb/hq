Frontend stack:
* Vue 3 (Composition API)
* Typescript
* Tailwind CSS v4
* Konva.js (and Vue Konva)
* Pinia for state management
* Reka UI
* Lucide Vue Next
* VueUse Core
* tw-animate-css
* Inertia.js

Prefer TypeScript interfaces for things that will be hydrated and dehydrated from the API - User role (player vs gamemaster), 
tile types, etc.

Backend stack:
* Laravel 12
* Inertia
* Laravel Wayfinder
* Laravel Sanctum

What: A web application for creating and playing HeroQuest quests.  The game board is
interactive and shared in real-time between the players and the game master (using websockets). Laravel
provides the backend (API) and Vue.js provides the frontend.

Current Domain: Board

Current Goal: Define the base game board. The game board is a grid system (default is 22x28, with an option to resize) 
of square tiles. An empty board consists of non-interactive tiles (consider the non-interacting tiles as walls or blocks). 

Notes for future updates, the game master will be able to create floor tiles (which are interactive) and place them on 
the board which will create rooms and corridors for the players to explore.  Floor tiles will have multiple layers (such 
as traps, monsters, the player's character, fixtures, etc.). At the start of play, a game master will either select an 
existing game board or create a new one. Keep these future goals in mind when designing the game board.