require 'bootstrap-sass'
require 'compass/import-once/activate'

http_path = "/skin/"
sass_dir = "scss"
css_dir = "css"
images_dir = "images"
javascripts_dir = "js"
fonts_dir = "css/fonts"
relative_assets = true

output_style = output_style = (environment == :production) ? :compressed : :expanded
