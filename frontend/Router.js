import React from 'react';
import {
    Router as ReactRouter,
    Route,
    browserHistory,
} from 'react-router';

import navigation from './navigation';

const Router = () => (
    <ReactRouter history={browserHistory}>
        <Route path="/" component={navigation.AppContainer} />
    </ReactRouter>
);

export default Router;
