const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));

gulp.task('build', function() {
  return gulp.src('src/scss/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('css'));
});

gulp.task('default', gulp.series('build'));
