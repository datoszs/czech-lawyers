import ga from 'react-ga';

const production = (process.env.NODE_ENV === 'production');

ga.initialize('UA-97836816-1', {
    debug: !production,
});

if (!production) {
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
