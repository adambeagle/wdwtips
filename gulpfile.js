var gulp = require('gulp'),
    sass = require('gulp-sass'),
    concat = require('gulp-concat'),
	uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    autoprefixer = require('gulp-autoprefixer');

// Watch SCSS and JS for changes
// Call minify tasks as needed
gulp.task('default', ['style', 'js'], function() {
    gulp.watch('wdwtips/static/scss/**/*.scss', ['style']);
    gulp.watch('wdwtips/static/js/**/!(wdwtips.min)*.js', ['js']);
});

// Concat and uglify JS
gulp.task('js', function() {
  // api.js must be first
  var js_files = [
    'wdwtips/static/js/api.js',
    'wdwtips/static/js/*.js',
    '!wdwtips/static/js/*.min.js',
  ];
  
  return gulp.src(js_files)
    .pipe(concat('wdwtips.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('wdwtips/static/js'))
});

// Compile, autoprefix, and minify SCSS
gulp.task('style', function() {
  return gulp.src('wdwtips/static/scss/**/*.scss')
      .pipe(sass({
          outputStyle: 'compressed'
      }).on('error', sass.logError))
      .pipe(autoprefixer({
          browsers: ['last 4 versions']
      }))
      .pipe(rename('style.min.css'))
      .pipe(gulp.dest('wdwtips/static/css/'))
});
