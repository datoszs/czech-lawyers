import React from 'react';
import {Provider} from 'react-redux';

import './index.less';

// eslint-disable-next-line react/prop-types, #scaffolding
const Root = ({store}) => (
    <Provider store={store}>
        <div>It works!</div>
    </Provider>
);

export default Root;
