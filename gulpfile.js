var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var clean = require('gulp-clean');
var expect = require('gulp-expect-file');
var path = require('path');
var less = require('gulp-less');
var merge2 = require('merge2');


// ====== Configuration =======
var paths = {
    scripts: [
        'resources/js/jquery-2.2.0.min.js',
        'resources/js/bootstrap.js',
        'resources/js/netteForms.min.js',
        'resources/js/selectize.min.js',
        'resources/js/application.js'
    ],
    styles: [
        'resources/css/bootstrap.css',
        'resources/css/selectize.bootstrap3.css',
        'resources/css/application.css'
    ],
    stylesLess: [
        'resources/css/administration.less'
    ],
    fonts: [
        'resources/fonts/**/*'
    ],
    images: [
        'resources/images/**/*'
    ]

};

var destinations = {
    scripts: 'www/assets/js/',
    styles: 'www/assets/css/',
    fonts: 'www/assets/fonts/',
    images: 'www/assets/images/'
};


// ====== General tasks =======
gulp.task('clean', function () {
    var arr=[];
    for( var i in destinations ) {
        if (destinations.hasOwnProperty(i)){
            arr.push(destinations[i]);
        }
    }
    return gulp.src(arr, {read: false})
        .pipe(clean());
});


// ====== Stylesheet preparation =======
gulp.task('styles', function() {
    var cssStream = gulp.src(paths.styles)
        .pipe(expect(paths.styles));
    var lessStream = gulp.src(paths.stylesLess)
        .pipe(expect(paths.stylesLess))
        .pipe(less({
            paths: [ path.join(__dirname) ]
        }));

    return merge2(cssStream, lessStream)
        .pipe(concat('style.css'))
        .pipe(gulp.dest(destinations.styles));

});

// ====== Fonts =======
gulp.task('fonts', function() {
    return gulp.src(paths.fonts)
        .pipe(expect(paths.fonts))
        .pipe(gulp.dest(destinations.fonts));
});

// ====== Images ======
gulp.task('images', function() {
    return gulp.src(paths.images)
        .pipe(expect(paths.images))
        .pipe(gulp.dest(destinations.images));
});

// ====== Javascript =======
gulp.task('scripts', function() {
    return gulp.src(paths.scripts)
        .pipe(concat('scripts.js'))
        .pipe(gulp.dest(destinations.scripts));
});
gulp.task('scripts-minify', function() {
    return gulp.src(paths.scripts)
        .pipe(concat('scripts.js'))
        .pipe(uglify())
        .pipe(gulp.dest(destinations.scripts));
});
gulp.task('scripts-full', ['scripts']);
gulp.task('scripts-mini', ['scripts']); // For now backend scripts are not minified, due to bug in uglify (which destroys output)

// ====== Other actions =======
gulp.task('watch', function() {
    gulp.watch(paths.styles, ['styles']);
    gulp.watch(paths.stylesLess, ['styles']);

    gulp.watch(paths.scripts, ['scripts']);

    gulp.watch(paths.fonts, ['fonts']);
    gulp.watch(paths.images, ['images']);

});

gulp.task('default', ['styles', 'scripts-full', 'images', 'fonts']);
gulp.task('default-mini', ['styles', 'scripts-mini', 'images', 'fonts']);

gulp.task('development', ['default', 'watch']);
gulp.task('production', ['default-mini']);
