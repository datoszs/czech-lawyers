import React from 'react';
import {render} from 'react-dom';
import {AppContainer} from 'react-hot-loader';

import store from './store';
import Root from './Root';

const renderApp = (RootComponent) => {
    render(
        <AppContainer>
            <RootComponent store={store} />
        </AppContainer>,
        document.getElementById('content'),
    );
};

renderApp(Root);

if (module.hot) {
    module.hot.accept('./Root', () => System.import('./Root').then((root) => renderApp(root.default)));
}
