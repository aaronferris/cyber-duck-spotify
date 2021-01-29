/**
 * @file
 * Gulp task runner for the Cyber Duck Spotify module.
 */

const gulp = require("gulp");
const path = require("path");
const sassLint = require('gulp-sass-lint');

const $ = require("gulp-load-plugins")();

/**
 * Compile sass files.
 */
function compile() {
  return gulp
    .src("sass/cyber-duck-spotify.scss")
    .pipe($.sass())
    .pipe(
      $.autoprefixer({
        overrideBrowserslist: ["last 2 versions"],
        cascade: false
      })
    )
    .pipe($.sass().on("error", $.sass.logError))
    .pipe(sassLint())
    .pipe(sassLint.format())
    .pipe(sassLint.failOnError())
    .pipe($.minifyCss())
    .pipe(gulp.dest("css"));
}

exports.compile = compile;
