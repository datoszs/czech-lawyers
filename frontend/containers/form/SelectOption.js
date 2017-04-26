import React from 'react';
import PropTypes from 'prop-types';
import {Field} from 'redux-form/immutable';
import {SelectOptionComponent} from '../../components/form';
import Msg from '../Msg';

const SelectOption = ({id, label}, {selectName}) => (
    <Field
        component={SelectOptionComponent}
        name={selectName}
        props={{
            children: <Msg msg={label} />,
            id,
        }}
    />
);

SelectOption.propTypes = {
    label: PropTypes.string.isRequired,
    id: PropTypes.string.isRequired,
};

SelectOption.contextTypes = {
    selectName: PropTypes.string.isRequired,
};

export default SelectOption;
