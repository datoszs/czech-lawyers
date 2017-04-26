import React from 'react';
import PropTypes from 'prop-types';
import {Address} from '../model';
import PostcodeFormatter from './PostcodeFormatter';

const AddressFormatter = ({value}) => (
    <div>
        {value.street}
        {(value.street && (value.city || value.postcode)) && <br />}
        {value.city}&emsp;<PostcodeFormatter value={value.postcode} />
    </div>
);

AddressFormatter.propTypes = {
    value: PropTypes.instanceOf(Address).isRequired,
};

export default AddressFormatter;
