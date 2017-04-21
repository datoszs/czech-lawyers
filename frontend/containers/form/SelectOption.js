import {PropTypes} from 'react';
import {connect} from 'react-redux';
import {Field} from 'redux-form/immutable';
import {SelectOptionComponent} from '../../components/form';
import translate from '../../translate';

const mapStateToProps = (state, {label}) => ({
    label: translate.getMessage(state, label),
});

const mergeProps = ({label}, dispatch, {name, id}) => ({
    component: SelectOptionComponent,
    name,
    props: {
        children: label,
        id,
    },
});

const SelectOption = connect(mapStateToProps, undefined, mergeProps)(Field);

SelectOption.propTypes = {
    label: PropTypes.string.isRequired,
    name: PropTypes.string.isRequired,
    id: PropTypes.string.isRequired,
};

export default SelectOption;
