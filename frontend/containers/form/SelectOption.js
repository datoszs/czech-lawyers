import React from 'react';
import PropTypes from 'prop-types';
import {Field} from 'redux-form/immutable';
import {SelectOptionComponent} from '../../components/form';
import Msg from '../Msg';
import selectFieldContextType from './selectFieldContextType';
import {isRequired} from './validations';

const SelectOption = ({id, label}, {selectField: {name, required}}) => (
    <Field
        component={SelectOptionComponent}
        name={name}
        validate={required ? isRequired : null}
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
    selectField: selectFieldContextType,
};

export default SelectOption;
