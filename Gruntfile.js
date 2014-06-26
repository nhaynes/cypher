module.exports = function(grunt)
{
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		phpunit: {
			test: {
				options: {
					bin: 'vendor/bin/phpunit'
				}
			}
		},

		watch: {
			options: {
				reload: true
			},
			
			config: {
				files: 'Gruntfile.js'
			},

			classes: {
				files: 'src/**/*.php',
				tasks: ['phpunit:test']
			},

			tests: {
				files: 'tests/**/*.php',
				tasks: ['phpunit:test']
			}
		}
	})

	grunt.loadNpmTasks('grunt-contrib-watch')
	grunt.loadNpmTasks('grunt-phpunit')

	grunt.registerTask('default', ['watch'])
	grunt.registerTask('test', ['phpunit:test'])
}