import React from 'react';
import {Provider} from 'react-redux';
import {Label} from 'react-bootstrap';

import './include.less';
import './index.less';

// eslint-disable-next-line react/prop-types, #scaffolding
const Root = ({store}) => (
    <Provider store={store}>
        <Label bsStyle="success">It works!</Label>
    </Provider>
);

export default Root;
