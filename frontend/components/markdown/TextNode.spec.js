import React from 'react';
import {shallow} from 'enzyme';
import TextNode from './TextNode';
import PositiveText from './PositiveText';
import NegativeText from './NegativeText';

describe('TextNode component', () => {
    it('displays simple positive text', () => {
        shallow(<TextNode literal="+positive+" />).should.contain(<PositiveText text="positive" />);
    });
    it('displays simple negative text', () => {
        shallow(<TextNode literal="-negative-" />).should.contain(<NegativeText text="negative" />);
    });
    it('displays unformatted text', () => {
        shallow(<TextNode literal="some text" />).should.have.text('some text');
    });
    it('recognizes escaped + inside', () => {
        shallow(<TextNode literal="+some \+ text+" />).should.contain(<PositiveText text="some \+ text" />);
    });
    it('recognizes escaped + at end', () => {
        shallow(<TextNode literal="+some text\+" />).should.have.text('+some text\\+');
    });
    it('recognizes escaped + at start', () => {
        shallow(<TextNode literal="\+some text+" />).should.have.text('\\+some text+');
    });
    it('recognizes escaped - inside', () => {
        shallow(<TextNode literal="-some \- text-" />).should.contain(<NegativeText text="some \- text" />);
    });
    it('recognizes escaped - at end', () => {
        shallow(<TextNode literal="-some text\-" />).should.have.text('-some text\\-');
    });
    it('recognizes escaped - at start', () => {
        shallow(<TextNode literal="\-some text-" />).should.have.text('\\-some text-');
    });
});
