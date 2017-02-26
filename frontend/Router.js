import React from 'react';
import {
    Router as ReactRouter,
    Route,
    browserHistory,
} from 'react-router';

import navigation from './navigation';

const Router = () => (
    <ReactRouter history={browserHistory}>
        <Route path="/" component={navigation.AppContainer} >
            <Route path="about" component={() => <h1>O projektu</h1>} />
            <Route path="contact" component={() => <h1>Kontakt</h1>} />
        </Route>
    </ReactRouter>
);

export default Router;
