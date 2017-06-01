import React from 'react';
import {shallow} from 'enzyme';
import If from './If';

/* eslint-disable react/jsx-boolean-value */
describe('If component helper', () => {
    const SimpleComponent = () => <div />;
    it('does not display anything when test is false', () => {
        // cannot use not.have.descendants
        shallow(<If test={false} Component={SimpleComponent} />).find(SimpleComponent).should.have.lengthOf(0);
    });
    it('displays component when test is true', () => {
        shallow(<If test={true} Component={SimpleComponent} />).should.have.descendants(SimpleComponent);
    });
    it('passes remaining props down', () => {
        const wrapper = shallow(<If test={true} Component={SimpleComponent} text="Sample" />);
        const component = wrapper.find(SimpleComponent);
        component.should.have.prop('text', 'Sample');
    });
});
