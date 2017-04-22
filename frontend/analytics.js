import ga from 'react-ga';

ga.initialize('UA-97836816-1', {
    debug: false,
});

if (process.env.NODE_ENV === 'development') {
    ga.set({sendHitTask: null});
}

if (window.performance) {
    const loadTime = Math.round(window.performance.now());
    ga.timing({
        category: 'Dependencies',
        variable: 'load',
        value: loadTime,
        label: 'JS Application',
    });
}
