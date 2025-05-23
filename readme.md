## Community with legends API

### Local development
```
cp .env.example .env
make init
make dev
```
Application will be running under [localhost:63861](localhost:63861) and [http://api.community-with-legends.mmr.localhost/](http://api.community-with-legends.mmr.localhost/) in traefik environment. If you don't have a traefik environment set up yet, follow the instructions from this [repository](https://github.com/MRR-Group/environment).

#### Commands
Before running any of the commands below, you must run shell:
```
make shell
```

| Command                 | Task                                        |
|:------------------------|:--------------------------------------------|
| `composer <command>`    | Composer                                    |
| `composer test`         | Runs backend tests                          |
| `composer analyse`      | Runs Larastan analyse for backend files     |
| `composer cs`           | Lints backend files                         |
| `composer csf`          | Lints and fixes backend files               |
| `php artisan <command>` | Artisan commands                            |
| `npm run dev`           | Compiles and hot-reloads for development    |
| `npm run build`         | Compiles and minifies for production        |

#### Containers

| service    | container name            | default host port               |
|:-----------|:--------------------------|:--------------------------------|
| `app`      | `community-with-legends-app-dev`     | [63861](http://localhost:63861) |
| `database` | `community-with-legends-db-dev`      | 63863                           |
| `redis`    | `community-with-legends-redis-dev`   | 63862                           |
| `mailpit`  | `community-with-legends-mailpit-dev` | 63864                           |
