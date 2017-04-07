# CodeRightBundle
A bundle to generate basic functional symfony code

# Installation
composer install nasetech/coderight-bundle

# Usage
1. Create Bundle
    curl -I http://127.0.0.1:8000/bundle?bundle=CodeRightBundle
2. Generate Metadata
    curl -I http://127.0.0.1:8000/metadata?className=CodeRightBundle\Entity\Example
3. Create Controller
    curl -I http://127.0.0.1:8000/controller?bundle=CodeRightBundle&className=CodeRightBundle\Entity\Example
4. Create Service
    curl -I http://127.0.0.1:8000/service?bundle=CodeRightBundle&className=CodeRightBundle\Entity\Example
5. Create repository
    curl -I http://127.0.0.1:8000/repository?bundle=CodeRightBundle&className=CodeRightBundle\Entity\Example