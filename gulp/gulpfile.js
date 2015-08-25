var gulp = require('gulp'),
   plugins = require('gulp-load-plugins')();

gulp.task('js', function () {
	gulp.src('js/*.js')
		.pipe(plugins.jshint())
		.pipe(plugins.jshint.reporter('default'))
		//.pipe(plugins.uglify())
		.pipe(plugins.concat('min.js'))
		.pipe(gulp.dest('../build/js'));
});

gulp.task("sass", function () {
	gulp.src('sass/*.scss')
		.pipe(plugins.sass())
		.pipe(plugins.minifyCss())
		.pipe(gulp.dest('../build/css'));
});

function handleError(err) {
  console.log(err.toString());
  this.emit('end');
}

gulp.task("build", ["js", "sass"]);

gulp.task("watch", function() {
	gulp.watch("js/*.js", ["js"]);
	gulp.watch("sass/**/*.scss", ["sass"]);
});