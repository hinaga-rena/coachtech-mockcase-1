# 使う docker コマンド（環境に合わせて上書き可）
DC ?= docker-compose

.PHONY: init fresh restart up down cache stop

init:
	$(DC) up -d --build
	$(DC) exec php composer install

	# .env が無いときだけコピー
	@if [ ! -f ./src/.env ]; then \
		$(DC) exec php cp .env.example .env; \
		echo "copied .env from .env.example"; \
	else \
		echo "skip: .env already exists"; \
	fi

	# 画像の移動（存在する時だけ実行）
	@if ls ./src/public/img/copy_storage_img/*.jpg >/dev/null 2>&1; then \
		mkdir -p ./src/storage/app/public/img; \
		mv ./src/public/img/copy_storage_img/*.jpg ./src/storage/app/public/img/; \
		echo "moved demo images to storage/app/public/img/"; \
	else \
		echo "skip: no demo images to move"; \
	fi

	$(DC) exec php php artisan key:generate
	$(DC) exec php php artisan storage:link
	$(DC) exec php chmod -R 777 storage bootstrap/cache

	@$(MAKE) fresh

fresh:
	$(DC) exec php php artisan migrate:fresh --seed

restart:
	@$(MAKE) down
	@$(MAKE) up

up:
	$(DC) up -d

down:
	$(DC) down --remove-orphans

cache:
	$(DC) exec php php artisan cache:clear
	$(DC) exec php php artisan config:cache

stop:
	$(DC) stop