<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

## Permissão para leitura e alteração de arquivos

- Você pode ler qualquer arquivo do projeto para entender o contexto, verificar dependências e manter consistência.
- Para escrita, siga o escopo de arquivos autorizado pelo agente ativo. Não altere arquivos fora desse escopo sem autorização explícita do usuário.
- Se uma alteração fora do escopo for necessária e ainda não tiver autorização explícita, informe os caminhos exatos e o motivo, peça autorização ao usuário e aguarde uma resposta afirmativa antes de editar. Use o mecanismo de pergunta disponível, como `ask_user`, quando possível.
- Um pedido genérico para implementar uma funcionalidade não autoriza alterações fora do escopo. Um pedido direto que identifique o caminho e a mudança pode ser tratado como autorização somente para esses itens. Peça nova autorização se o escopo mudar.
- Se a autorização for negada ou não vier, não altere esses arquivos; adapte a solução ou informe a dependência bloqueada.
