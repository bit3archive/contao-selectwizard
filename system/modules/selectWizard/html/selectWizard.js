/**
 * Select Wizard
 * Copyright (C) 2011 Tristan Lins
 *
 * Extension for:
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 * 
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  InfinitySoft 2011
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    Select Wizard
 * @license    LGPL
 * @filesource
 */

if (!Backend.selectWizard) Backend.selectWizard = function(el, command, id)
{
	var table = $(id);
	var tbody = table.getElement('tbody');
	var parent = $(el).getParent('tr');
	var rows = tbody.getChildren();

	Backend.getScrollOffset();

	switch (command)
	{
		case 'copy':
			var tr = new Element('tr');
			var childs = parent.getChildren();

			for (var i=0; i<childs.length; i++)
			{
				var next = childs[i].clone(true).injectInside(tr);
				next.getFirst().value = childs[i].getFirst().value;
			}

			tr.injectAfter(parent);
			break;

		case 'up':
			parent.getPrevious() ? parent.injectBefore(parent.getPrevious()) : parent.injectInside(tbody);
			break;

		case 'down':
			parent.getNext() ? parent.injectAfter(parent.getNext()) : parent.injectBefore(tbody.getFirst());
			break;

		case 'delete':
			(rows.length > 1) ? parent.destroy() : null;
			break;
	}

	rows = tbody.getChildren();

	for (var i=0; i<rows.length; i++)
	{
		var childs = rows[i].getChildren();

		for (var j=0; j<childs.length; j++)
		{
			var first = childs[j].getFirst();

			if (first.type == 'text' || first.type == 'select-one')
			{
				first.name = first.name.replace(/\[[0-9]+\]/ig, '[' + i + ']');
			}
		}
	}
};